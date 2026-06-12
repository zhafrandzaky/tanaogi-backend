<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AccommodationResource;
use App\Services\AccommodationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AccommodationService $accommodationService
    ) {}

    public function index(): JsonResponse
    {
        $accommodations = $this->accommodationService->getAll();

        return $this->success(
            'Data penginapan berhasil diambil',
            AccommodationResource::collection($accommodations)
        );
    }

    public function byDestination(string $slug): JsonResponse
    {
        $accommodations = $this->accommodationService->getByDestination($slug);

        return $this->success(
            'Data penginapan berhasil diambil',
            AccommodationResource::collection($accommodations)
        );
    }
}
