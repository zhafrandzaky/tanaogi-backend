<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'regency_id'   => $this->regency_id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'ticket_price' => $this->ticket_price,
            'facilities'   => $this->facilities,
            'route_text'   => $this->route_text,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'is_active'    => $this->is_active,
            'images'         => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])->values()),
            'regency'        => RegencyResource::make($this->whenLoaded('regency')),
            'accommodations' => $this->whenLoaded('accommodations', fn () => $this->accommodations->map(fn ($acc) => [
                'id'       => $acc->id,
                'name'     => $acc->name,
                'type'     => ucfirst($acc->type),
                'desc'     => $acc->type === 'resort' ? 'Resort premium dengan fasilitas dan pemandangan indah.' : ($acc->type === 'hotel' ? 'Hotel modern nyaman dekat lokasi wisata.' : 'Homestay tradisional kelolaan warga lokal.'),
                'price'    => 'Rp ' . number_format($acc->price_per_night, 0, ',', '.'),
                'address'  => $acc->address,
                'latitude' => $acc->latitude,
                'longitude'=> $acc->longitude,
            ])->values()),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
