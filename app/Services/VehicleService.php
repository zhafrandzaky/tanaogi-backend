<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleService
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->vehicleRepository->findAll();
    }

    public function getAllActive(): Collection
    {
        return $this->vehicleRepository->findAllActive();
    }

    public function findById(string $id): Vehicle
    {
        $vehicle = $this->vehicleRepository->findById($id);

        if (!$vehicle) {
            throw new ModelNotFoundException('Kendaraan tidak ditemukan');
        }

        return $vehicle;
    }

    public function create(array $data): Vehicle
    {
        $data['is_active'] = $data['is_active'] ?? true;
        return $this->vehicleRepository->create($data);
    }

    public function update(string $id, array $data): Vehicle
    {
        $vehicle = $this->findById($id);
        return $this->vehicleRepository->update($vehicle, $data);
    }

    public function delete(string $id): bool
    {
        $vehicle = $this->findById($id);
        return $this->vehicleRepository->delete($vehicle);
    }

    public function toggleActive(string $id): Vehicle
    {
        $vehicle = $this->findById($id);
        return $this->vehicleRepository->update($vehicle, ['is_active' => !$vehicle->is_active]);
    }
}
