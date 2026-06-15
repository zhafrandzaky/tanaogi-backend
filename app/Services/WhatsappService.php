<?php

namespace App\Services;

use App\Models\DriverOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Kirim pesan WhatsApp via WaAPI.
     *
     * @param  string  $phone   Nomor telepon format internasional (628xxx)
     * @param  string  $message Isi pesan
     * @return bool true jika berhasil, false jika gagal
     */
    public function send(string $phone, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key'    => config('services.waapi.key'),
            ])->post(config('services.waapi.url') . '/api/whatsapp/send-message-premium', [
                'number'  => $phone,
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->failed() || ($body['success'] ?? false) === false) {
                Log::error('WaAPI send failed', [
                    'phone'    => $phone,
                    'status'   => $response->status(),
                    'response' => $body,
                ]);

                return false;
            }

            // Rate limit: 1 pesan per 30 detik per API key
            sleep(1);

            return true;
        } catch (\Throwable $e) {
            Log::error('WaAPI send exception', [
                'phone'   => $phone,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Kirim reminder ke driver berdasarkan DriverOrder.
     *
     * @param  DriverOrder  $order
     * @return bool
     */
    public function sendReminderToDriver(DriverOrder $order): bool
    {
        $order->loadMissing(['driver']);

        $driver = $order->driver;

        if (!$driver || !$driver->phone) {
            Log::warning('sendReminderToDriver: driver or phone missing', [
                'order_id' => $order->id,
            ]);

            return false;
        }

        $message = $this->buildReminderMessage($order);

        return $this->send($driver->phone, $message);
    }

    /**
     * Build pesan reminder berdasarkan is_overnight flag.
     */
    protected function buildReminderMessage(DriverOrder $order): string
    {
        if ($order->is_overnight) {
            // PP/Menginap H-1
            return implode("\n", [
                'Reminder TanaOgi',
                'Besok kamu perlu menjemput penumpang:',
                'Nama    : ' . $order->user_name,
                'Lokasi  : ' . $order->pickup_location,
                'Tanggal : ' . $order->return_date->format('d-m-Y'),
                'No HP   : ' . $order->user_phone,
                'Pastikan hadir tepat waktu.',
            ]);
        }

        // One-day trip
        return implode("\n", [
            'Reminder TanaOgi',
            'Kamu perlu menjemput penumpang hari ini:',
            'Nama    : ' . $order->user_name,
            'Lokasi  : ' . $order->pickup_location,
            'No HP   : ' . $order->user_phone,
            'Pastikan hadir tepat waktu.',
        ]);
    }
}
