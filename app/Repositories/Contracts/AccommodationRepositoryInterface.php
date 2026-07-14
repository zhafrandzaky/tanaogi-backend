<?php

namespace App\Repositories\Contracts;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Collection;

interface AccommodationRepositoryInterface
{
    public function findAll(): Collection;
    public function findByDestinationId(string $destinationId): Collection;
    public function findById(string $id): ?Accommodation;
    public function create(array $data): Accommodation;
    public function update(Accommodation $accommodation, array $data): Accommodation;
    public function delete(Accommodation $accommodation): bool;
    public function paginate(int $perPage, ?string $search = null, ?string $type = null, ?string $status = null, ?string $destinationId = null);
}
