<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class MaintenanceService
{
    public function __construct(private SettingService $settingService) {}

    public function enable(): void
    {
        Artisan::call('down', [
            '--secret' => config('app.maintenance_secret'),
        ]);

        $this->settingService->update(['is_maintenance' => 'true']);
    }

    public function disable(): void
    {
        Artisan::call('up');

        $this->settingService->update(['is_maintenance' => 'false']);
    }

    public function isActive(): bool
    {
        return filter_var($this->settingService->get('is_maintenance'), FILTER_VALIDATE_BOOLEAN);
    }
}
