<?php

namespace App\Repositories\Contracts;

interface RequestLogRepositoryInterface
{
    public function create(array $data): void;

    public function countByIpSince(string $ip, int $minutes): int;

    public function countByPhoneSince(string $phone, int $hours): int;

    public function deleteOlderThan(int $hours): int;
}
