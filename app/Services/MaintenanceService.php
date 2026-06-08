<?php

namespace App\Services;

class MaintenanceService
{
    public function __construct(private SettingService $settingService) {}

    public function enable(): void
    {
        $this->settingService->update(['is_maintenance' => 'true']);
    }

    public function disable(): void
    {
        $this->settingService->update(['is_maintenance' => 'false']);
    }

    public function isActive(): bool
    {
        return filter_var($this->settingService->get('is_maintenance'), FILTER_VALIDATE_BOOLEAN);
    }
}
