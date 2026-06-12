<?php

namespace App\Services;

use App\Exceptions\DriverNotAvailableException;
use App\Exceptions\PhoneBlacklistedException;
use App\Models\DriverOrder;
use App\Repositories\Contracts\DriverOrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DriverOrderService
{
    public function __construct(
        private readonly DriverOrderRepositoryInterface $driverOrderRepository,
        private readonly DriverService $driverService,
        private readonly BlacklistService $blacklistService,
        private readonly RateLimitService $rateLimitService,
    ) {}

    public function getAll(?string $status = null): Collection
    {
        return $this->driverOrderRepository->findAll($status);
    }

    public function findById(string $id): DriverOrder
    {
        $order = $this->driverOrderRepository->findById($id);

        if (!$order) {
            throw new ModelNotFoundException('Driver order tidak ditemukan');
        }

        return $order;
    }

    public function create(array $data): DriverOrder
    {
        $phone = $data['user_phone'];

        // Step 1: Check phone whitelist → if not whitelisted, check blacklist
        if (!$this->blacklistService->isPhoneWhitelisted($phone)) {
            if ($this->blacklistService->isPhoneBlacklisted($phone)) {
                throw new PhoneBlacklistedException();
            }
        }

        // Step 2: Log phone via RateLimitService (may auto-ban)
        $this->rateLimitService->checkAndLogPhone($phone, 'driver-order.create');

        // Step 3: Check again after log (might have been just auto-banned)
        if (!$this->blacklistService->isPhoneWhitelisted($phone)) {
            if ($this->blacklistService->isPhoneBlacklisted($phone)) {
                throw new PhoneBlacklistedException();
            }
        }

        // Step 4: Create order in DB
        $data['status'] = $data['status'] ?? 'pending';

        return $this->driverOrderRepository->create($data);
    }

    public function assignDriver(DriverOrder $order, string $driverId): DriverOrder
    {
        // Step 1: Find driver and check active
        $driver = $this->driverService->findById($driverId);

        if (!$driver->is_active) {
            throw new DriverNotAvailableException('Driver tidak aktif');
        }

        // Step 2: Check availability for both dates
        $departureDate = $order->departure_date->format('Y-m-d');
        $returnDate    = $order->return_date->format('Y-m-d');
        $vehicleType   = $order->vehicle->type->value;

        $availableDrivers = $this->driverService->getAvailable(
            $departureDate,
            $returnDate,
            $vehicleType
        );

        // Step 3: Check if driverId is in available list
        $isAvailable = $availableDrivers->contains('id', $driverId);

        if (!$isAvailable) {
            throw new DriverNotAvailableException(
                'Driver tidak tersedia pada tanggal yang dipilih'
            );
        }

        // Step 4: Assign driver and update status to confirmed
        return $this->driverOrderRepository->assignDriver($order, $driverId);
    }

    public function updateStatus(DriverOrder $order, string $status): DriverOrder
    {
        return $this->driverOrderRepository->updateStatus($order, $status);
    }

    public function markReturnReminded(DriverOrder $order): void
    {
        $this->driverOrderRepository->markReturnReminded($order);
    }

    public function update(string $id, array $data): DriverOrder
    {
        $order = $this->findById($id);

        // If driver_id is provided, run assign driver logic
        if (isset($data['driver_id'])) {
            return $this->assignDriver($order, $data['driver_id']);
        }

        // If only status is provided, update status
        if (isset($data['status'])) {
            return $this->updateStatus($order, $data['status']);
        }

        return $this->driverOrderRepository->update($order, $data);
    }

    public function delete(string $id): bool
    {
        $order = $this->findById($id);
        return $this->driverOrderRepository->delete($order);
    }
}
