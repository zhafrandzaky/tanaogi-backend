<?php

namespace App\Repositories;

use App\Models\Destination;
use App\Repositories\Contracts\DestinationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DestinationRepository implements DestinationRepositoryInterface
{
    public function findAll(): Collection
    {
        return Destination::with(['regency', 'images'])->orderBy('name')->get();
    }

    public function findAllActive(): Collection
    {
        return Destination::with(['regency', 'images'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findByRegency(string $regencyId): Collection
    {
        return Destination::with(['regency', 'images'])
            ->where('regency_id', $regencyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?Destination
    {
        return Destination::with(['regency', 'images', 'accommodations'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function findById(string $id): ?Destination
    {
        return Destination::with(['regency', 'images', 'accommodations'])->find($id);
    }

    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $query = Destination::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(array $data): Destination
    {
        return Destination::create($data);
    }

    public function update(Destination $destination, array $data): Destination
    {
        $destination->update($data);
        return $destination->fresh(['regency', 'images']);
    }

    public function delete(Destination $destination): bool
    {
        return $destination->delete();
    }

    public function paginate(int $perPage, ?string $search = null, ?string $status = null, ?string $regencyId = null)
    {
        $query = Destination::with(['regency', 'images']);
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active' || $status === '1' || $status === 'true');
        }
        if ($regencyId) {
            $query->where('regency_id', $regencyId);
        }
        return $query->orderBy('name')->paginate($perPage);
    }
}
