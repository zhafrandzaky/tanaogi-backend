<?php

namespace App\Repositories;

use App\Models\Accommodation;
use App\Repositories\Contracts\AccommodationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AccommodationRepository implements AccommodationRepositoryInterface
{
    public function findAll(): Collection
    {
        return Accommodation::with('destination')->orderBy('name')->get();
    }

    public function findByDestinationId(string $destinationId): Collection
    {
        return Accommodation::where('destination_id', $destinationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?Accommodation
    {
        return Accommodation::with('destination')->find($id);
    }

    public function create(array $data): Accommodation
    {
        return Accommodation::create($data);
    }

    public function update(Accommodation $accommodation, array $data): Accommodation
    {
        $accommodation->update($data);
        return $accommodation->fresh('destination');
    }

    public function delete(Accommodation $accommodation): bool
    {
        return $accommodation->delete();
    }
}
