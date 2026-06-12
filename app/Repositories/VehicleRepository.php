<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function findAll(): Collection
    {
        return Vehicle::orderBy('name')->get();
    }

    public function findAllActive(): Collection
    {
        return Vehicle::where('is_active', true)->orderBy('name')->get();
    }

    public function findById(string $id): ?Vehicle
    {
        return Vehicle::find($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);
        return $vehicle->fresh();
    }

    public function delete(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }
}
