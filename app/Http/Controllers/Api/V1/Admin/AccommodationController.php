<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAccommodationRequest;
use App\Http\Requests\Admin\UpdateAccommodationRequest;
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

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');
        $type = $request->query('type');
        $status = $request->query('status');
        $destinationId = $request->query('destination_id');

        $paginator = $this->accommodationService->paginate($perPage, $search, $type, $status, $destinationId);

        return $this->paginated(
            'Data penginapan berhasil diambil',
            $paginator,
            AccommodationResource::collection($paginator->items())
        );
    }

    public function store(StoreAccommodationRequest $request): JsonResponse
    {
        $accommodation = $this->accommodationService->create($request->validated());

        return $this->success(
            'Penginapan berhasil dibuat',
            AccommodationResource::make($accommodation),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $accommodation = $this->accommodationService->findById($id);

        return $this->success(
            'Detail penginapan berhasil diambil',
            AccommodationResource::make($accommodation)
        );
    }

    public function update(UpdateAccommodationRequest $request, string $id): JsonResponse
    {
        $accommodation = $this->accommodationService->update($id, $request->validated());

        return $this->success(
            'Penginapan berhasil diperbarui',
            AccommodationResource::make($accommodation)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->accommodationService->delete($id);

        return $this->success('Penginapan berhasil dihapus');
    }

    public function toggleActive(string $id): JsonResponse
    {
        $accommodation = $this->accommodationService->toggleActive($id);

        return $this->success(
            'Status berhasil diubah',
            ['id' => $accommodation->id, 'is_active' => $accommodation->is_active]
        );
    }
}
