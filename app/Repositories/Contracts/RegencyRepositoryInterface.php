<?php

namespace App\Repositories\Contracts;

use App\Models\Regency;
use Illuminate\Database\Eloquent\Collection;

interface RegencyRepositoryInterface
{
    public function findAll(): Collection;
    public function findAllActive(): Collection;
    public function findById(string $id): ?Regency;
    public function create(array $data): Regency;
    public function update(Regency $regency, array $data): Regency;
    public function delete(Regency $regency): bool;
}
