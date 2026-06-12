<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        $vehicles = $this->vehicleService->getAllActive();

        return $this->success(
            'Data kendaraan berhasil diambil',
            VehicleResource::collection($vehicles)
        );
    }
}
