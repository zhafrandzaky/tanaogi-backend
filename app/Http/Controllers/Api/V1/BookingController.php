<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class BookingController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination_id'   => 'required',
            'visit_date'       => 'required|date',
            'pax_count'        => 'required|integer|min:1',
            'has_driver'       => 'required|boolean',
            'driver_package'   => 'nullable|string',
            'driver_price'     => 'nullable|numeric',
            'driver_id'        => 'nullable|uuid|exists:drivers,id',
            'include_hotel'    => 'required|boolean',
            'selected_hotel'   => 'nullable|string',
            'hotel_price'      => 'nullable|numeric',
            'accommodation_id' => 'nullable|uuid|exists:accommodations,id',
            'customer_name'    => 'required|string',
            'customer_phone'   => 'required|string',
            'notes'            => 'nullable|string',
            'payment_method'   => 'nullable|string',
        ]);

        // Find database destination to associate correctly
        $dest = Destination::where('slug', $validated['destination_id'])
            ->orWhere('id', $validated['destination_id'])
            ->first();

        // Calculate ticket onsite fallback
        $ticketPrice = $dest ? $dest->ticket_price : 15000; // default Tanjung Bira ticket
        $entranceFee = $ticketPrice * $validated['pax_count'];

        // Calculate total amount to pay via web (excluding ticket price)
        $driverPrice = $validated['has_driver'] ? ($validated['driver_price'] ?? 0) : 0;
        $hotelPrice = $validated['include_hotel'] ? ($validated['hotel_price'] ?? 0) : 0;
        $totalWeb = $driverPrice + $hotelPrice;

        // Save Booking in Pending State
        $booking = Booking::create([
            'user_id'                    => $request->user()?->id,
            'destination_id'             => $dest?->id,
            'destination_slug'           => $dest?->slug ?? $validated['destination_id'],
            'visit_date'                 => $validated['visit_date'],
            'pax_count'                  => $validated['pax_count'],
            'has_driver'                 => $validated['has_driver'],
            'driver_package'             => $validated['driver_package'],
            'driver_price'               => $driverPrice,
            'driver_id'                  => $validated['driver_id'] ?? null,
            'include_hotel'              => $validated['include_hotel'],
            'selected_hotel'             => $validated['selected_hotel'],
            'hotel_price'                => $hotelPrice,
            'accommodation_id'           => $validated['accommodation_id'] ?? null,
            'total_amount_web'           => $totalWeb,
            'entrance_ticket_fee_onsite' => $entranceFee,
            'payment_status'             => 'pending',
            'customer_name'              => $validated['customer_name'],
            'customer_phone'             => $validated['customer_phone'],
            'notes'                      => $validated['notes'],
        ]);

        $booking->load(['driver', 'accommodation']);

        // If nothing to pay on web, complete booking instantly (e.g. self-driving, no hotel selected)
        if ($totalWeb <= 0) {
            $booking->update(['payment_status' => 'paid']);
            return response()->json([
                'success'    => true,
                'booking'    => $booking,
                'snap_token' => null
            ]);
        }

        // Initialize Midtrans Snap Transaction
        $params = [
            'transaction_details' => [
                'order_id'     => $booking->id,
                'gross_amount' => (int) $totalWeb,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'phone'      => $booking->customer_phone,
                'email'      => $request->user()?->email ?? 'customer@tanaogi.com',
            ],
            'item_details' => []
        ];

        if ($validated['has_driver'] && $driverPrice > 0) {
            $params['item_details'][] = [
                'id'       => 'driver_' . ($validated['driver_package'] ?? 'custom'),
                'price'    => (int) $driverPrice,
                'quantity' => 1,
                'name'     => 'Driver Lokal - ' . ($validated['driver_package'] ?? 'Paket Driver'),
            ];
        }

        if ($validated['include_hotel'] && $hotelPrice > 0) {
            $params['item_details'][] = [
                'id'       => 'hotel_' . strtolower(str_replace(' ', '_', $validated['selected_hotel'])),
                'price'    => (int) $hotelPrice,
                'quantity' => 1,
                'name'     => 'Hotel - ' . $validated['selected_hotel'],
            ];
        }

        // Check if using default dummy Server Key or empty
        $serverKey = config('services.midtrans.server_key');
        $isDummy = empty($serverKey) || str_contains($serverKey, 'lhjX-tI1x93mJ4Z25fD6H8lA') || str_contains($serverKey, 'your_key_here');

        if ($isDummy) {
            $snapToken = 'mock-snap-token-' . Str::random(20);
            $booking->update(['midtrans_snap_token' => $snapToken]);
            // Refresh model to return updated status
            $booking->refresh();

            return response()->json([
                'success'    => true,
                'booking'    => $booking,
                'snap_token' => $snapToken,
                'is_mock'    => true
            ]);
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update(['midtrans_snap_token' => $snapToken]);

            return response()->json([
                'success'    => true,
                'booking'    => $booking,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice Midtrans: ' . $e->getMessage()
            ], 500);
        }

        $enabledPayments = match ($validated['payment_method'] ?? null) {
            'va' => ['bank_transfer'],
            'qris' => ['qris'],
            'ewallet' => ['gopay', 'dana', 'shopeepay'],
            'card' => ['credit_card'],
            default => null,
        };
        if ($enabledPayments) {
            $params['enabled_payments'] = $enabledPayments;
        }
    }

    /**
     * Retry / regenerate snap token for a pending booking.
     * This allows the user to complete a previously failed or cancelled payment.
     */
    public function retryPayment($id, Request $request)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'pending')
            ->firstOrFail();

        // If total_amount_web is 0, mark paid directly
        if ($booking->total_amount_web <= 0) {
            $booking->update(['payment_status' => 'paid']);
            return response()->json([
                'success'    => true,
                'booking'    => $booking->fresh(),
                'snap_token' => null,
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
        $isDummy = empty($serverKey) || str_contains($serverKey, 'lhjX-tI1x93mJ4Z25fD6H8lA') || str_contains($serverKey, 'your_key_here');

        if ($isDummy) {
            $snapToken = 'mock-snap-token-' . Str::random(20);
            $booking->update(['midtrans_snap_token' => $snapToken]);
            return response()->json([
                'success'    => true,
                'booking'    => $booking->fresh(),
                'snap_token' => $snapToken,
                'is_mock'    => true,
            ]);
        }

        // Build Midtrans params
        // order_id must be ≤ 50 chars — use a short hash to ensure uniqueness
        $retryOrderId = 'R-' . substr(md5($booking->id . time()), 0, 20);
        $params = [
            'transaction_details' => [
                'order_id'     => $retryOrderId, // e.g. "R-3f2a8c..." — max 22 chars
                'gross_amount' => (int) $booking->total_amount_web,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'phone'      => $booking->customer_phone,
                'email'      => $request->user()?->email ?? 'customer@tanaogi.com',
            ],
            'item_details' => [],
        ];

        if ($booking->has_driver && $booking->driver_price > 0) {
            $params['item_details'][] = [
                'id'       => 'driver_retry',
                'price'    => (int) $booking->driver_price,
                'quantity' => 1,
                'name'     => 'Driver Lokal - ' . ($booking->driver_package ?? 'Paket Driver'),
            ];
        }

        if ($booking->include_hotel && $booking->hotel_price > 0) {
            $params['item_details'][] = [
                'id'       => 'hotel_retry',
                'price'    => (int) $booking->hotel_price,
                'quantity' => 1,
                'name'     => 'Hotel - ' . ($booking->selected_hotel ?? 'Penginapan'),
            ];
        }

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->update(['midtrans_snap_token' => $snapToken]);

            return response()->json([
                'success'    => true,
                'booking'    => $booking->fresh(),
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat ulang invoice Midtrans: ' . $e->getMessage()
            ], 500);
        }
    }

    public function paymentConfig()
    {
        $serverKey = config('services.midtrans.server_key');
        $clientKey = config('services.midtrans.client_key');
        $isDummy = empty($serverKey) || empty($clientKey) || str_contains($serverKey, 'lhjX-tI1x93mJ4Z25fD6H8lA') || str_contains($serverKey, 'your_key_here');

        return response()->json([
            'enabled' => ! $isDummy,
            'client_key' => $isDummy ? null : $clientKey,
            'snap_url' => config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    public function completeMockPayment(Booking $booking, Request $request)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $serverKey = config('services.midtrans.server_key');
        $isDummy = empty($serverKey) || str_contains($serverKey, 'lhjX-tI1x93mJ4Z25fD6H8lA') || str_contains($serverKey, 'your_key_here');

        abort_unless($isDummy && str_starts_with((string) $booking->midtrans_snap_token, 'mock-snap-token-'), 404);

        $booking->update(['payment_status' => 'paid']);

        return response()->json([
            'success' => true,
            'booking' => $booking->fresh(),
        ]);
    }

    public function index(Request $request)
    {
        $bookings = Booking::with(['destination:id,title,slug'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'  => true,
            'bookings' => $bookings
        ]);
    }

    public function show($id, Request $request)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    public function handleWebhook(Request $request)
    {
        try {
            $notification = new Notification();
            
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status;
            
            $booking = Booking::find($orderId);
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }
            
            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $booking->payment_status = 'pending';
                    } else {
                        $booking->payment_status = 'paid';
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                $booking->payment_status = 'paid';
            } else if ($transactionStatus == 'pending') {
                $booking->payment_status = 'pending';
            } else if ($transactionStatus == 'deny') {
                $booking->payment_status = 'failed';
            } else if ($transactionStatus == 'expire') {
                $booking->payment_status = 'expired';
            } else if ($transactionStatus == 'cancel') {
                $booking->payment_status = 'cancelled';
            }
            
            $booking->midtrans_transaction_id = $notification->transaction_id;
            $booking->save();
            
            return response()->json(['message' => 'Webhook handled successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
