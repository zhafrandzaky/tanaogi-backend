<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationRequest;
use App\Http\Requests\Admin\UploadDestinationImageRequest;
use App\Http\Resources\V1\DestinationResource;
use App\Services\DestinationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DestinationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DestinationService $destinationService
    ) {}

    public function index(): JsonResponse
    {
        $destinations = $this->destinationService->getAll();

        return $this->success(
            'Data destinasi berhasil diambil',
            DestinationResource::collection($destinations)
        );
    }

    public function store(StoreDestinationRequest $request): JsonResponse
    {
        $destination = $this->destinationService->create($request->validated());

        return $this->success(
            'Destinasi berhasil dibuat',
            DestinationResource::make($destination),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $destination = $this->destinationService->findById($id);

        return $this->success(
            'Detail destinasi berhasil diambil',
            DestinationResource::make($destination)
        );
    }

    public function update(UpdateDestinationRequest $request, string $id): JsonResponse
    {
        $destination = $this->destinationService->update($id, $request->validated());

        return $this->success(
            'Destinasi berhasil diperbarui',
            DestinationResource::make($destination)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->destinationService->delete($id);

        return $this->success('Destinasi berhasil dihapus');
    }

    public function toggleActive(string $id): JsonResponse
    {
        $destination = $this->destinationService->toggleActive($id);

        return $this->success(
            'Status berhasil diubah',
            ['id' => $destination->id, 'is_active' => $destination->is_active]
        );
    }

    public function uploadImages(UploadDestinationImageRequest $request, string $id): JsonResponse
    {
        $images = $this->destinationService->uploadImages($id, $request->file('images'));

        return $this->success(
            'Foto berhasil diupload',
            $images->map(fn ($image) => [
                'id'    => $image->id,
                'url'   => $image->url,
                'order' => $image->order,
            ])->values()
        );
    }

    public function deleteImage(string $id, string $imageId): JsonResponse
    {
        $this->destinationService->deleteImage($id, $imageId);

        return $this->success('Foto berhasil dihapus');
    }
}
