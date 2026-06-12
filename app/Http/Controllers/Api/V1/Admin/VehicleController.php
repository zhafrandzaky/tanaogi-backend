<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVehicleRequest;
use App\Http\Requests\Admin\UpdateVehicleRequest;
use App\Http\Resources\V1\VehicleResource;
use App\Services\VehicleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly VehicleService $vehicleService
    ) {}

    public function index(): JsonResponse
    {
        $vehicles = $this->vehicleService->getAll();

        return $this->success(
            'Data kendaraan berhasil diambil',
            VehicleResource::collection($vehicles)
        );
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return $this->success(
            'Kendaraan berhasil dibuat',
            VehicleResource::make($vehicle),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $vehicle = $this->vehicleService->findById($id);

        return $this->success(
            'Detail kendaraan berhasil diambil',
            VehicleResource::make($vehicle)
        );
    }

    public function update(UpdateVehicleRequest $request, string $id): JsonResponse
    {
        $vehicle = $this->vehicleService->update($id, $request->validated());

        return $this->success(
            'Kendaraan berhasil diperbarui',
            VehicleResource::make($vehicle)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->vehicleService->delete($id);

        return $this->success('Kendaraan berhasil dihapus');
    }

    public function toggleActive(string $id): JsonResponse
    {
        $vehicle = $this->vehicleService->toggleActive($id);

        return $this->success(
            'Status berhasil diubah',
            ['id' => $vehicle->id, 'is_active' => $vehicle->is_active]
        );
    }
}
