<?php

namespace App\Services;

use App\Enums\DriverOrderStatus;
use App\Models\DriverOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function __construct(
        private readonly WhatsappService $whatsappService,
        private readonly SettingService $settingService,
    ) {}

    /**
     * Main entry point — called by scheduler every minute.
     */
    public function processReminders(): void
    {
        $this->processOneDayTripReminders();
        $this->processOvernightReminders();
    }

    /**
     * One-day trip (is_overnight = false):
     * Kirim reminder X jam sebelum penjemputan pulang (default jam 17.00).
     * Dicek setiap menit oleh scheduler.
     */
    private function processOneDayTripReminders(): void
    {
        $hoursBeforePickup = (int) ($this->settingService->get('reminder_hours_before_pickup') ?? 3);

        $orders = DriverOrder::query()
            ->where('is_overnight', false)
            ->whereDate('return_date', today())
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->where('status', DriverOrderStatus::CONFIRMED)
            ->get();

        foreach ($orders as $order) {
            // Asumsi penjemputan pulang jam 17.00 (default)
            $pickupTime = Carbon::parse($order->return_date->format('Y-m-d') . ' 17:00:00');
            $reminderTime = $pickupTime->copy()->subHours($hoursBeforePickup);

            // Window 1 menit agar scheduler mencocokkan tepat waktu
            if (now()->between($reminderTime, $reminderTime->copy()->addMinute())) {
                $this->sendReturnReminder($order);
            }
        }
    }

    /**
     * PP / Menginap (is_overnight = true):
     * Kirim reminder H-1 sebelum tanggal pulang, jam 07.00 pagi.
     */
    private function processOvernightReminders(): void
    {
        // Hanya jalankan sekali sehari jam 07.00
        if (now()->format('H:i') !== '07:00') {
            return;
        }

        $tomorrow = today()->addDay();

        $orders = DriverOrder::query()
            ->where('is_overnight', true)
            ->whereDate('return_date', $tomorrow)
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->where('status', DriverOrderStatus::CONFIRMED)
            ->get();

        foreach ($orders as $order) {
            $this->sendReturnReminder($order);
        }
    }

    /**
     * Kirim reminder via WhatsappService.
     * Jika berhasil → set return_reminded = true.
     * Jika gagal → log error, biarkan false agar retry di menit berikutnya.
     */
    private function sendReturnReminder(DriverOrder $order): void
    {
        $sent = $this->whatsappService->sendReminderToDriver($order);

        if ($sent) {
            $order->update(['return_reminded' => true]);
            Log::info("Reminder sent to driver for order {$order->id}");
        } else {
            Log::error("Failed to send reminder for order {$order->id}");
        }
    }
}
