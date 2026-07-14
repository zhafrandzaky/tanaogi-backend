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
        if (\Illuminate\Support\Str::isUuid($slug)) {
            $destination = $this->destinationService->findById($slug);
        } else {
            try {
                $destination = $this->destinationService->findBySlug($slug);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $destination = $this->destinationService->findById($slug);
            }
        }

        return $this->success(
            'Detail destinasi berhasil diambil',
            DestinationResource::make($destination)
        );
    }
}
