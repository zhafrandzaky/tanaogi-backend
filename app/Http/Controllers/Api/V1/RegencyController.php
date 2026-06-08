<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RegencyResource;
use App\Services\RegencyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RegencyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RegencyService $regencyService
    ) {}

    public function index(): JsonResponse
    {
        $regencies = $this->regencyService->getAllActive();

        return $this->success(
            'Data kabupaten berhasil diambil',
            RegencyResource::collection($regencies)
        );
    }
}
