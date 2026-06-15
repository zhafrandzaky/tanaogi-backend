<?php

namespace App\Repositories;

use App\Enums\DriverOrderStatus;
use App\Models\DriverOrder;
use App\Repositories\Contracts\DriverOrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DriverOrderRepository implements DriverOrderRepositoryInterface
{
    public function findAll(?string $status = null): Collection
    {
        $query = DriverOrder::with(['destination', 'vehicle', 'driver', 'accommodation'])
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function findById(string $id): ?DriverOrder
    {
        return DriverOrder::with(['destination', 'vehicle', 'driver', 'accommodation'])->find($id);
    }

    public function create(array $data): DriverOrder
    {
        return DriverOrder::create($data);
    }

    public function update(DriverOrder $order, array $data): DriverOrder
    {
        $order->update($data);
        return $order->fresh(['destination', 'vehicle', 'driver', 'accommodation']);
    }

    public function delete(DriverOrder $order): bool
    {
        return $order->delete();
    }

    public function assignDriver(DriverOrder $order, string $driverId): DriverOrder
    {
        $order->update([
            'driver_id' => $driverId,
            'status'    => DriverOrderStatus::CONFIRMED,
        ]);

        return $order->fresh(['destination', 'vehicle', 'driver', 'accommodation']);
    }

    public function updateStatus(DriverOrder $order, string $status): DriverOrder
    {
        $order->update(['status' => $status]);
        return $order->fresh(['destination', 'vehicle', 'driver', 'accommodation']);
    }

    public function markReturnReminded(DriverOrder $order): DriverOrder
    {
        $order->update(['return_reminded' => true]);
        return $order->fresh();
    }

    public function findPendingOneDayReminders(): Collection
    {
        return DriverOrder::query()
            ->where('is_overnight', false)
            ->whereDate('return_date', today())
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->where('status', DriverOrderStatus::CONFIRMED)
            ->get();
    }

    public function findPendingOvernightReminders(Carbon $date): Collection
    {
        return DriverOrder::query()
            ->where('is_overnight', true)
            ->whereDate('return_date', $date)
            ->whereNotNull('driver_id')
            ->where('return_reminded', false)
            ->where('status', DriverOrderStatus::CONFIRMED)
            ->get();
    }
}
