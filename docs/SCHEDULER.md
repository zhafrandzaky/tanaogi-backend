# SCHEDULER.md — Sistem Reminder Driver TanaOgi

## Overview

Laravel Scheduler berjalan setiap menit dan mengecek apakah ada reminder yang perlu dikirim ke driver via WhatsApp (WaAPI — https://waapi.fyas.my.id).

Ada 2 jenis reminder:

| Jenis | Kapan Dikirim | Ke Siapa |
|---|---|---|
| One-day trip | X jam sebelum penjemputan pulang | Driver yang assigned |
| PP / Menginap | H-1 sebelum tanggal pulang, jam 07.00 | Driver yang assigned |

---

## Setup Scheduler

`app/Console/Kernel.php` (atau via `routes/console.php` di Laravel 13):

```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    app(ReminderService::class)->processReminders();
})->everyMinute()->name('process-driver-reminders')->withoutOverlapping();
```

Atau menggunakan Command:
```bash
php artisan make:command SendDriverReminders
```

```php
// app/Console/Commands/SendDriverReminders.php
class SendDriverReminders extends Command
{
    protected $signature = 'reminders:send-driver';
    protected $description = 'Kirim reminder ke driver yang perlu menjemput';

    public function handle(ReminderService $reminderService): void
    {
        $reminderService->processReminders();
        $this->info('Reminder processed at ' . now());
    }
}
```

```php
// routes/console.php
Schedule::command('reminders:send-driver')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

---

## Logic ReminderService

```php
// app/Services/ReminderService.php

class ReminderService
{
    public function __construct(
        private readonly DriverOrderService $driverOrderService,
        private readonly WhatsappService $whatsappService,
        private readonly SettingService $settingService,
    ) {}

    public function processReminders(): void
    {
        $this->processOneDayTripReminders();
        $this->processOvernightReminders();
    }

    /**
     * One-day trip: user tidak menginap
     * Kirim reminder X jam sebelum waktu penjemputan
     * Scheduler cek setiap menit
     */
    private function processOneDayTripReminders(): void
    {
        $hoursBeforePickup = (int) $this->settingService->get('reminder_hours_before_pickup');

        // Cari order yang:
        // - is_overnight = false (one-day trip)
        // - return_date = hari ini
        // - driver sudah assigned
        // - return_reminded = false
        // - waktu sekarang = return_date - X jam (dalam range 1 menit)
        $orders = DriverOrder::query()
            ->where('is_overnight', false)
            ->whereDate('return_date', today())
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->whereIn('status', ['confirmed'])
            ->get();

        foreach ($orders as $order) {
            // Hitung kapan seharusnya reminder dikirim
            // Asumsi: penjemputan jam 17.00 (bisa dikonfigurasi)
            $pickupTime = Carbon::parse($order->return_date->format('Y-m-d') . ' 17:00:00');
            $reminderTime = $pickupTime->subHours($hoursBeforePickup);

            // Kirim jika waktu sekarang sudah masuk window 1 menit
            if (now()->between($reminderTime, $reminderTime->addMinute())) {
                $this->sendReturnReminder($order);
            }
        }
    }

    /**
     * PP / Menginap: user menginap
     * Kirim reminder H-1 sebelum tanggal pulang jam 07.00 pagi
     */
    private function processOvernightReminders(): void
    {
        // Hanya jalankan sekali sehari jam 07.00
        if (now()->format('H:i') !== '07:00') {
            return;
        }

        $tomorrow = today()->addDay();

        // Cari order yang:
        // - is_overnight = true
        // - return_date = besok (H-1)
        // - driver sudah assigned
        // - return_reminded = false
        // - status = confirmed
        $orders = DriverOrder::query()
            ->where('is_overnight', true)
            ->whereDate('return_date', $tomorrow)
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->where('status', 'confirmed')
            ->with(['driver', 'destination', 'accommodation'])
            ->get();

        foreach ($orders as $order) {
            $this->sendReturnReminder($order);
        }
    }

    private function sendReturnReminder(DriverOrder $order): void
    {
        if (!$order->driver) {
            return;
        }

        $message = $this->buildReminderMessage($order);
        $sent = $this->whatsappService->send($order->driver->phone, $message);

        if ($sent) {
            $order->update(['return_reminded' => true]);
            Log::info("Reminder sent to driver {$order->driver->name} for order {$order->id}");
        } else {
            Log::error("Failed to send reminder for order {$order->id}");
        }
    }

    private function buildReminderMessage(DriverOrder $order): string
    {
        $returnDate = Carbon::parse($order->return_date)->translatedFormat('d F Y');

        if ($order->is_overnight) {
            // PP / Menginap — H-1
            return "Reminder TanaOgi 🔔\n\n"
                . "Besok kamu perlu menjemput penumpang:\n\n"
                . "👤 Nama    : {$order->user_name}\n"
                . "📌 Lokasi  : {$order->pickup_location}\n"
                . "📅 Tanggal : {$returnDate}\n"
                . "📞 No HP   : {$order->user_phone}\n\n"
                . "Pastikan hadir tepat waktu ya!";
        }

        // One-day trip
        return "Reminder TanaOgi 🔔\n\n"
            . "Kamu perlu menjemput penumpang hari ini:\n\n"
            . "👤 Nama    : {$order->user_name}\n"
            . "📌 Lokasi  : {$order->pickup_location}\n"
            . "📞 No HP   : {$order->user_phone}\n\n"
            . "Pastikan hadir tepat waktu ya!";
    }
}
```

---

## Alur Lengkap Reminder

### One-Day Trip

```
Order dibuat (is_overnight = false)
return_date = 20 Juni 2025
        ↓
Scheduler jalan setiap menit
        ↓
Jam 14.00 (3 jam sebelum pickup jam 17.00)
        ↓
Cek: return_date = hari ini?  ✅
Cek: is_overnight = false?    ✅
Cek: driver assigned?         ✅
Cek: return_reminded = false? ✅
Cek: waktu = 14.00?           ✅
        ↓
Kirim WA ke driver via Fonnte
        ↓
Update return_reminded = true
(tidak akan kirim lagi)
```

### PP / Menginap

```
Order dibuat (is_overnight = true)
return_date = 22 Juni 2025
        ↓
Scheduler jalan setiap menit
        ↓
21 Juni jam 07.00 (H-1)
        ↓
Cek: jam 07.00?               ✅
Cek: return_date = besok?     ✅
Cek: is_overnight = true?     ✅
Cek: driver assigned?         ✅
Cek: return_reminded = false? ✅
        ↓
Kirim WA ke driver via Fonnte
        ↓
Update return_reminded = true
```

---

## Konfigurasi di Settings

| Key | Default | Deskripsi |
|---|---|---|
| `reminder_hours_before_pickup` | `3` | Jam sebelum penjemputan untuk one-day trip |

Admin bisa ubah via endpoint:
```
PUT /api/v1/admin/settings
{ "reminder_hours_before_pickup": 2 }
```

---

## Testing Scheduler Lokal

```bash
# Jalankan scheduler sekali (untuk test)
php artisan schedule:run

# Jalankan command reminder langsung
php artisan reminders:send-driver

# Lihat semua scheduled jobs
php artisan schedule:list
```

---

## Fail Handling

Jika Fonnte API gagal (timeout, error):
- Log error ke Laravel log
- `return_reminded` tetap `false` — scheduler akan coba lagi di menit berikutnya
- Setelah berhasil, baru set `return_reminded = true`

Pastikan Fonnte token valid dan nomor WA driver format benar (`628xxx`).
