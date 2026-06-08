<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingService
{
    public function get(string $key): ?string
    {
        return Setting::where('key', $key)->value('value');
    }

    public function getAll(): Collection
    {
        return Setting::all();
    }

    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function getAdminWhatsapp(): string
    {
        return (string) $this->get('admin_whatsapp');
    }

    public function getWaTemplate(): string
    {
        return (string) $this->get('wa_template');
    }

    public function getReminderHours(): int
    {
        return (int) $this->get('reminder_hours');
    }

    public function isAutoBanEnabled(): bool
    {
        return filter_var($this->get('auto_ban_enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    public function getMaxRequestsPerMinute(): int
    {
        return (int) $this->get('max_requests_per_minute');
    }

    public function getMaxOrdersPerPhone(): int
    {
        return (int) $this->get('max_orders_per_phone');
    }

    public function getOrdersWindowHours(): int
    {
        return (int) $this->get('orders_window_hours');
    }

    public function getAutoBanDuration(): int
    {
        return (int) $this->get('auto_ban_duration_minutes');
    }
}
