<?php

namespace App\Services;

use App\Models\Driver;
use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DriverService
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->driverRepository->findAll();
    }

    public function getAllActive(): Collection
    {
        return $this->driverRepository->findAllActive();
    }

    public function findById(string $id): Driver
    {
        $driver = $this->driverRepository->findById($id);

        if (!$driver) {
            throw new ModelNotFoundException('Driver tidak ditemukan');
        }

        return $driver;
    }

    public function create(array $data): Driver
    {
        $data['is_active'] = $data['is_active'] ?? true;
        return $this->driverRepository->create($data);
    }

    public function update(string $id, array $data): Driver
    {
        $driver = $this->findById($id);
        return $this->driverRepository->update($driver, $data);
    }

    public function delete(string $id): bool
    {
        $driver = $this->findById($id);
        return $this->driverRepository->delete($driver);
    }

    public function toggleActive(string $id): Driver
    {
        $driver = $this->findById($id);
        return $this->driverRepository->update($driver, ['is_active' => !$driver->is_active]);
    }

    public function getAvailable(string $departureDate, string $returnDate, string $vehicleType): Collection
    {
        return $this->driverRepository->findAvailable($departureDate, $returnDate, $vehicleType);
    }

    public function getSchedule(string $driverId, int $month, int $year): array
    {
        $driver = $this->findById($driverId);
        $orders = $this->driverRepository->findScheduleByMonth($driverId, $month, $year);

        $blockedDates = [];
        $orderDetails = [];

        foreach ($orders as $order) {
            $departureDate = $order->departure_date->format('Y-m-d');
            $returnDate    = $order->return_date->format('Y-m-d');

            if ((int) $order->departure_date->format('m') === $month && (int) $order->departure_date->format('Y') === $year) {
                if (!in_array($departureDate, $blockedDates)) {
                    $blockedDates[] = $departureDate;
                }
                $orderDetails[] = [
                    'date'      => $departureDate,
                    'type'      => 'departure',
                    'order_id'  => $order->id,
                    'user_name' => $order->user_name,
                ];
            }

            if ((int) $order->return_date->format('m') === $month && (int) $order->return_date->format('Y') === $year) {
                if (!in_array($returnDate, $blockedDates)) {
                    $blockedDates[] = $returnDate;
                }
                $orderDetails[] = [
                    'date'      => $returnDate,
                    'type'      => 'return',
                    'order_id'  => $order->id,
                    'user_name' => $order->user_name,
                ];
            }
        }

        sort($blockedDates);

        return [
            'driver_id'     => $driver->id,
            'driver_name'   => $driver->name,
            'month'         => $month,
            'year'          => $year,
            'blocked_dates' => $blockedDates,
            'orders'        => $orderDetails,
        ];
    }
}
