<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Repositories\Contracts\AccommodationRepositoryInterface;
use App\Repositories\Contracts\DestinationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccommodationService
{
    public function __construct(
        private readonly AccommodationRepositoryInterface $accommodationRepository,
        private readonly DestinationRepositoryInterface $destinationRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->accommodationRepository->findAll();
    }

    public function getByDestination(string $slug): Collection
    {
        $destination = $this->destinationRepository->findBySlug($slug);

        if (!$destination) {
            throw new ModelNotFoundException('Destinasi tidak ditemukan');
        }

        return $this->accommodationRepository->findByDestinationId($destination->id);
    }

    public function findById(string $id): Accommodation
    {
        $accommodation = $this->accommodationRepository->findById($id);

        if (!$accommodation) {
            throw new ModelNotFoundException('Penginapan tidak ditemukan');
        }

        return $accommodation;
    }

    public function create(array $data): Accommodation
    {
        $data['is_active'] = $data['is_active'] ?? true;
        return $this->accommodationRepository->create($data);
    }

    public function update(string $id, array $data): Accommodation
    {
        $accommodation = $this->findById($id);
        return $this->accommodationRepository->update($accommodation, $data);
    }

    public function delete(string $id): bool
    {
        $accommodation = $this->findById($id);
        return $this->accommodationRepository->delete($accommodation);
    }

    public function toggleActive(string $id): Accommodation
    {
        $accommodation = $this->findById($id);
        return $this->accommodationRepository->update($accommodation, ['is_active' => !$accommodation->is_active]);
    }

    public function paginate(int $perPage, ?string $search = null, ?string $type = null, ?string $status = null, ?string $destinationId = null)
    {
        return $this->accommodationRepository->paginate($perPage, $search, $type, $status, $destinationId);
    }
}
