<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MaintenanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {}

    public function status(): JsonResponse
    {
        return $this->success(
            'Status maintenance berhasil diambil',
            ['is_maintenance' => $this->maintenanceService->isActive()]
        );
    }
}
