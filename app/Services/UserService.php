<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->userRepository->findAll();
    }

    public function findById(string $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new ModelNotFoundException('User tidak ditemukan');
        }

        return $user;
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        $user->assignRole('admin');

        return $user->load('roles');
    }

    public function update(string $id, array $data): User
    {
        $user = $this->findById($id);
        return $this->userRepository->update($user, $data);
    }

    public function delete(string $id, string $currentUserId): bool
    {
        if ($id === $currentUserId) {
            throw new \InvalidArgumentException('Tidak dapat menghapus akun sendiri');
        }

        $user = $this->findById($id);
        return $this->userRepository->delete($user);
    }
}
