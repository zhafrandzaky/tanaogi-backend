<?php

namespace App\Services;

use App\Models\Regency;
use App\Repositories\Contracts\RegencyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class RegencyService
{
    public function __construct(
        private readonly RegencyRepositoryInterface $regencyRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->regencyRepository->findAll();
    }

    public function getAllActive(): Collection
    {
        return $this->regencyRepository->findAllActive();
    }

    public function findById(string $id): Regency
    {
        $regency = $this->regencyRepository->findById($id);

        if (!$regency) {
            throw new ModelNotFoundException('Kabupaten tidak ditemukan');
        }

        return $regency;
    }

    public function create(array $data): Regency
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->regencyRepository->create($data);
    }

    public function update(string $id, array $data): Regency
    {
        $regency = $this->findById($id);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->regencyRepository->update($regency, $data);
    }

    public function delete(string $id): bool
    {
        $regency = $this->findById($id);
        return $this->regencyRepository->delete($regency);
    }

    public function toggleActive(string $id): Regency
    {
        $regency = $this->findById($id);
        return $this->regencyRepository->update($regency, ['is_active' => !$regency->is_active]);
    }
}
