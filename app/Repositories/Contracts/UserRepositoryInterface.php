<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findAll(): \Illuminate\Database\Eloquent\Collection;
    public function findByEmail(string $email): ?User;
    public function findById(string $id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
}
