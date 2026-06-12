<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'type'            => $this->type,
            'price_per_night' => $this->price_per_night,
            'address'         => $this->address,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'is_active'       => $this->is_active,
            'destination'     => $this->whenLoaded('destination', fn () => [
                'id'   => $this->destination->id,
                'name' => $this->destination->name,
                'slug' => $this->destination->slug,
            ]),
            'created_at'      => $this->created_at->toISOString(),
        ];
    }
}
