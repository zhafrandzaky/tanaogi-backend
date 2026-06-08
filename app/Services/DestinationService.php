<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\DestinationImage;
use App\Repositories\Contracts\DestinationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DestinationService
{
    public function __construct(
        private readonly DestinationRepositoryInterface $destinationRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->destinationRepository->findAll();
    }

    public function getByRegency(?string $regencyId): Collection
    {
        if ($regencyId) {
            return $this->destinationRepository->findByRegency($regencyId);
        }

        return $this->destinationRepository->findAllActive();
    }

    public function findBySlug(string $slug): Destination
    {
        $destination = $this->destinationRepository->findBySlug($slug);

        if (!$destination) {
            throw new ModelNotFoundException('Destinasi tidak ditemukan');
        }

        return $destination;
    }

    public function findById(string $id): Destination
    {
        $destination = $this->destinationRepository->findById($id);

        if (!$destination) {
            throw new ModelNotFoundException('Destinasi tidak ditemukan');
        }

        return $destination;
    }

    public function create(array $data): Destination
    {
        $data['slug'] = $this->generateSlug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->destinationRepository->create($data);
    }

    public function update(string $id, array $data): Destination
    {
        $destination = $this->findById($id);

        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug($data['name'], $destination->id);
        }

        return $this->destinationRepository->update($destination, $data);
    }

    public function delete(string $id): bool
    {
        $destination = $this->findById($id);

        return $this->destinationRepository->delete($destination);
    }

    public function toggleActive(string $id): Destination
    {
        $destination = $this->findById($id);

        return $this->destinationRepository->update($destination, [
            'is_active' => !$destination->is_active,
        ]);
    }

    public function uploadImages(string $id, array $files): Collection
    {
        $destination = $this->findById($id);
        $uploaded = collect();

        foreach ($files as $file) {
            $path = "destinations/{$destination->id}/" . Str::uuid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('r2')->put($path, file_get_contents($file), 'public');

            $url = Storage::disk('r2')->url($path);

            $image = DestinationImage::create([
                'destination_id' => $destination->id,
                'path'           => $path,
                'url'            => $url,
                'order'          => $destination->images()->count(),
            ]);

            $uploaded->push($image);
        }

        return $uploaded;
    }

    public function deleteImage(string $destinationId, string $imageId): bool
    {
        $this->findById($destinationId);

        $image = DestinationImage::findOrFail($imageId);

        Storage::disk('r2')->delete($image->path);

        return $image->delete();
    }

    public function generateSlug(string $name, ?string $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while ($this->destinationRepository->slugExists($slug, $excludeId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
