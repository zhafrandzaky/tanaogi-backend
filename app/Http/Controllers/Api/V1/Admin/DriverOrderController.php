<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\DriverOrderStatus;
use App\Exceptions\DriverNotAvailableException;
use App\Exceptions\PhoneBlacklistedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDriverOrderRequest;
use App\Http\Requests\Admin\UpdateDriverOrderRequest;
use App\Http\Resources\V1\DriverOrderResource;
use App\Services\DriverOrderService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DriverOrderService $driverOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $orders = $this->driverOrderService->getAll($status);

        return $this->success(
            'Data driver order berhasil diambil',
            DriverOrderResource::collection($orders)
        );
    }

    public function store(StoreDriverOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->driverOrderService->create($request->validated());
        } catch (PhoneBlacklistedException $e) {
            return $this->error($e->getMessage(), null, 403);
        }

        $order = $this->driverOrderService->findById($order->id);

        return $this->success(
            'Driver order berhasil dibuat',
            DriverOrderResource::make($order),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        try {
            $order = $this->driverOrderService->findById($id);
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        }

        return $this->success(
            'Detail driver order berhasil diambil',
            DriverOrderResource::make($order)
        );
    }

    public function update(UpdateDriverOrderRequest $request, string $id): JsonResponse
    {
        try {
            $order = $this->driverOrderService->update($id, $request->validated());
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        } catch (DriverNotAvailableException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return $this->success(
            'Driver order berhasil diperbarui',
            DriverOrderResource::make($order)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->driverOrderService->delete($id);
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        }

        return $this->success('Driver order berhasil dihapus');
    }

    public function confirm(string $id): JsonResponse
    {
        try {
            $order = $this->driverOrderService->findById($id);
            $order = $this->driverOrderService->updateStatus($order, DriverOrderStatus::CONFIRMED->value);
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        }

        return $this->success(
            'Status berhasil diubah',
            ['id' => $order->id, 'status' => $order->status->value]
        );
    }

    public function complete(string $id): JsonResponse
    {
        try {
            $order = $this->driverOrderService->findById($id);
            $order = $this->driverOrderService->updateStatus($order, DriverOrderStatus::COMPLETED->value);
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        }

        return $this->success(
            'Status berhasil diubah',
            ['id' => $order->id, 'status' => $order->status->value]
        );
    }

    public function cancel(string $id): JsonResponse
    {
        try {
            $order = $this->driverOrderService->findById($id);
            $order = $this->driverOrderService->updateStatus($order, DriverOrderStatus::CANCELLED->value);
        } catch (ModelNotFoundException $e) {
            return $this->error('Driver order tidak ditemukan', null, 404);
        }

        return $this->success(
            'Status berhasil diubah',
            ['id' => $order->id, 'status' => $order->status->value]
        );
    }
}
