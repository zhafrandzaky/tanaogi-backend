<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Http\Resources\V1\DriverResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DriverController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return $this->success(
            'Data driver berhasil diambil',
            DriverResource::collection($drivers)
        );
    }
}
