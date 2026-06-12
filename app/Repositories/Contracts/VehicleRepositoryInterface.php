<?php

namespace App\Repositories\Contracts;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function findAll(): Collection;
    public function findAllActive(): Collection;
    public function findById(string $id): ?Vehicle;
    public function create(array $data): Vehicle;
    public function update(Vehicle $vehicle, array $data): Vehicle;
    public function delete(Vehicle $vehicle): bool;
}
