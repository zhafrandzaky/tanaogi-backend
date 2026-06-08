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
            'images'       => $this->whenLoaded('images', fn () => $this->images->pluck('url')->values()),
            'regency'      => RegencyResource::make($this->whenLoaded('regency')),
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
