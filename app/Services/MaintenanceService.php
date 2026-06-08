<?php

namespace App\Services;

class MaintenanceService
{
    public function __construct(private SettingService $settingService) {}

    public function enable(): void
    {
        $this->settingService->update(['maintenance_mode' => 'true']);
    }

    public function disable(): void
    {
        $this->settingService->update(['maintenance_mode' => 'false']);
    }

    public function isActive(): bool
    {
        return filter_var($this->settingService->get('maintenance_mode'), FILTER_VALIDATE_BOOLEAN);
    }
}
