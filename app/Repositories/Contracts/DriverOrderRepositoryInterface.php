<?php

namespace App\Repositories\Contracts;

use App\Models\DriverOrder;
use Illuminate\Database\Eloquent\Collection;

interface DriverOrderRepositoryInterface
{
    public function findAll(?string $status = null): Collection;
    public function findById(string $id): ?DriverOrder;
    public function create(array $data): DriverOrder;
    public function update(DriverOrder $order, array $data): DriverOrder;
    public function delete(DriverOrder $order): bool;
    public function assignDriver(DriverOrder $order, string $driverId): DriverOrder;
    public function updateStatus(DriverOrder $order, string $status): DriverOrder;
    public function markReturnReminded(DriverOrder $order): DriverOrder;
}
