<?php

namespace App\Services;

use App\Repositories\Contracts\RequestLogRepositoryInterface;

class RateLimitService
{
    public function __construct(
        private BlacklistService $blacklistService,
        private SettingService $settingService,
        private RequestLogRepositoryInterface $requestLogRepository,
    ) {}

    public function checkAndLogIp(string $ip, string $endpoint): void
    {
        if ($this->blacklistService->isIpWhitelisted($ip)) {
            return;
        }

        $this->requestLogRepository->create([
            'ip_address' => $ip,
            'endpoint'   => $endpoint,
            'created_at' => now(),
        ]);

        if (!$this->settingService->isAutoBanEnabled()) {
            return;
        }

        $maxPerMinute = $this->settingService->getMaxRequestsPerMinute();
        $count = $this->getIpRequestCount($ip, 1);

        if ($count > $maxPerMinute) {
            $duration = $this->settingService->getAutoBanDuration();
            $this->blacklistService->banIp(
                $ip,
                "Auto-ban: {$count} request dalam 1 menit (limit: {$maxPerMinute})",
                true,
                $duration ?: null
            );
        }
    }

    public function checkAndLogPhone(string $phone, string $endpoint): void
    {
        if ($this->blacklistService->isPhoneWhitelisted($phone)) {
            return;
        }

        $this->requestLogRepository->create([
            'ip_address' => request()->ip(),
            'phone'      => $phone,
            'endpoint'   => $endpoint,
            'created_at' => now(),
        ]);

        if (!$this->settingService->isAutoBanEnabled()) {
            return;
        }

        $maxOrders   = $this->settingService->getMaxOrdersPerPhone();
        $windowHours = $this->settingService->getOrdersWindowHours();
        $count       = $this->getPhoneOrderCount($phone, $windowHours);

        if ($count > $maxOrders) {
            $duration = $this->settingService->getAutoBanDuration();
            $this->blacklistService->banPhone(
                $phone,
                "Auto-ban: {$count} order dalam {$windowHours} jam (limit: {$maxOrders})",
                true,
                $duration ?: null
            );
        }
    }

    public function getIpRequestCount(string $ip, int $minutes): int
    {
        return $this->requestLogRepository->countByIpSince($ip, $minutes);
    }

    public function getPhoneOrderCount(string $phone, int $hours): int
    {
        return $this->requestLogRepository->countByPhoneSince($phone, $hours);
    }

    public function cleanOldLogs(): void
    {
        $this->requestLogRepository->deleteOlderThan(24);
    }
}
