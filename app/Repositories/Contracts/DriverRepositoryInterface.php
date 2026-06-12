<?php

namespace App\Repositories\Contracts;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Collection;

interface DriverRepositoryInterface
{
    public function findAll(): Collection;
    public function findAllActive(): Collection;
    public function findById(string $id): ?Driver;
    public function create(array $data): Driver;
    public function update(Driver $driver, array $data): Driver;
    public function delete(Driver $driver): bool;
    public function findAvailable(string $departureDate, string $returnDate, string $vehicleType): Collection;
    public function findScheduleByMonth(string $driverId, int $month, int $year): Collection;
}
