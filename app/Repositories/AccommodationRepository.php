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

    public function paginate(int $perPage, ?string $search = null, ?string $type = null, ?string $status = null, ?string $destinationId = null)
    {
        $query = Accommodation::with('destination');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active' || $status === '1' || $status === 'true');
        }
        if ($destinationId) {
            $query->where('destination_id', $destinationId);
        }
        return $query->orderBy('name')->paginate($perPage);
    }
}
