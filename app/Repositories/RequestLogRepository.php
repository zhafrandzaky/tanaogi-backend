<?php

namespace App\Repositories;

use App\Models\RequestLog;
use App\Repositories\Contracts\RequestLogRepositoryInterface;

class RequestLogRepository implements RequestLogRepositoryInterface
{
    public function create(array $data): void
    {
        RequestLog::create($data);
    }

    public function countByIpSince(string $ip, int $minutes): int
    {
        return RequestLog::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public function countByPhoneSince(string $phone, int $hours): int
    {
        return RequestLog::where('phone', $phone)
            ->where('created_at', '>=', now()->subHours($hours))
            ->count();
    }

    public function deleteOlderThan(int $hours): int
    {
        return RequestLog::where('created_at', '<', now()->subHours($hours))->delete();
    }
}
