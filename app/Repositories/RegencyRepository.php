<?php

namespace App\Repositories;

use App\Models\Regency;
use App\Repositories\Contracts\RegencyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RegencyRepository implements RegencyRepositoryInterface
{
    public function findAll(): Collection
    {
        return Regency::orderBy('name')->get();
    }

    public function findAllActive(): Collection
    {
        return Regency::where('is_active', true)->orderBy('name')->get();
    }

    public function findById(string $id): ?Regency
    {
        return Regency::find($id);
    }

    public function create(array $data): Regency
    {
        return Regency::create($data);
    }

    public function update(Regency $regency, array $data): Regency
    {
        $regency->update($data);
        return $regency->fresh();
    }

    public function delete(Regency $regency): bool
    {
        return $regency->delete();
    }
}
