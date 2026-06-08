<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRegencyRequest;
use App\Http\Requests\Admin\UpdateRegencyRequest;
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
        $regencies = $this->regencyService->getAll();

        return $this->success(
            'Data kabupaten berhasil diambil',
            RegencyResource::collection($regencies)
        );
    }

    public function store(StoreRegencyRequest $request): JsonResponse
    {
        $regency = $this->regencyService->create($request->validated());

        return $this->success(
            'Kabupaten berhasil dibuat',
            RegencyResource::make($regency),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $regency = $this->regencyService->findById($id);

        return $this->success(
            'Detail kabupaten berhasil diambil',
            RegencyResource::make($regency)
        );
    }

    public function update(UpdateRegencyRequest $request, string $id): JsonResponse
    {
        $regency = $this->regencyService->update($id, $request->validated());

        return $this->success(
            'Kabupaten berhasil diperbarui',
            RegencyResource::make($regency)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->regencyService->delete($id);

        return $this->success('Kabupaten berhasil dihapus');
    }

    public function toggleActive(string $id): JsonResponse
    {
        $regency = $this->regencyService->toggleActive($id);

        return $this->success(
            'Status berhasil diubah',
            ['id' => $regency->id, 'is_active' => $regency->is_active]
        );
    }
}
