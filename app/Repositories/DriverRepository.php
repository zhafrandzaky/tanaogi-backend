<?php

namespace App\Repositories;

use App\Enums\DriverOrderStatus;
use App\Models\Driver;
use App\Models\DriverOrder;
use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DriverRepository implements DriverRepositoryInterface
{
    public function findAll(): Collection
    {
        return Driver::orderBy('name')->get();
    }

    public function findAllActive(): Collection
    {
        return Driver::where('is_active', true)->orderBy('name')->get();
    }

    public function findById(string $id): ?Driver
    {
        return Driver::find($id);
    }

    public function create(array $data): Driver
    {
        return Driver::create($data);
    }

    public function update(Driver $driver, array $data): Driver
    {
        $driver->update($data);
        return $driver->fresh();
    }

    public function delete(Driver $driver): bool
    {
        return $driver->delete();
    }

    public function findAvailable(string $departureDate, string $returnDate, string $vehicleType): Collection
    {
        return Driver::where('vehicle_type', $vehicleType)
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) use ($departureDate, $returnDate) {
                $query->select('driver_id')
                    ->from('driver_orders')
                    ->whereNotNull('driver_id')
                    ->whereNotIn('status', [
                        DriverOrderStatus::COMPLETED,
                        DriverOrderStatus::CANCELLED,
                    ])
                    ->where(function ($q) use ($departureDate, $returnDate) {
                        $q->where('departure_date', $departureDate)
                          ->orWhere('return_date', $returnDate);
                    });
            })
            ->orderBy('name')
            ->get();
    }

    public function findScheduleByMonth(string $driverId, int $month, int $year): Collection
    {
        return DriverOrder::where('driver_id', $driverId)
            ->whereNotIn('status', [
                DriverOrderStatus::COMPLETED,
                DriverOrderStatus::CANCELLED,
            ])
            ->where(function ($query) use ($month, $year) {
                $query->whereRaw('MONTH(departure_date) = ? AND YEAR(departure_date) = ?', [$month, $year])
                      ->orWhereRaw('MONTH(return_date) = ? AND YEAR(return_date) = ?', [$month, $year]);
            })
            ->with(['destination', 'vehicle'])
            ->orderBy('departure_date')
            ->get();
    }
}
