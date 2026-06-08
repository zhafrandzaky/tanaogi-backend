<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DestinationResource;
use App\Services\DestinationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DestinationService $destinationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $destinations = $this->destinationService->getByRegency($request->regency_id);

        return $this->success(
            'Data destinasi berhasil diambil',
            DestinationResource::collection($destinations)
        );
    }

    public function show(string $slug): JsonResponse
    {
        $destination = $this->destinationService->findBySlug($slug);

        return $this->success(
            'Detail destinasi berhasil diambil',
            DestinationResource::make($destination)
        );
    }
}
