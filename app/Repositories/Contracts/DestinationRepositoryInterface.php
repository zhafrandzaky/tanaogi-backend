<?php

namespace App\Repositories\Contracts;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Collection;

interface DestinationRepositoryInterface
{
    public function findAll(): Collection;
    public function findAllActive(): Collection;
    public function findByRegency(string $regencyId): Collection;
    public function findBySlug(string $slug): ?Destination;
    public function findById(string $id): ?Destination;
    public function slugExists(string $slug, ?string $excludeId = null): bool;
    public function create(array $data): Destination;
    public function update(Destination $destination, array $data): Destination;
    public function delete(Destination $destination): bool;
}
