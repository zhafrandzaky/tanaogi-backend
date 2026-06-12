<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'destination'        => DestinationResource::make($this->whenLoaded('destination')),
            'vehicle'            => VehicleResource::make($this->whenLoaded('vehicle')),
            'driver'             => DriverResource::make($this->whenLoaded('driver')),
            'accommodation'      => AccommodationResource::make($this->whenLoaded('accommodation')),
            'user_name'          => $this->user_name,
            'user_phone'         => $this->user_phone,
            'departure_date'     => $this->departure_date->format('Y-m-d'),
            'return_date'        => $this->return_date->format('Y-m-d'),
            'is_overnight'       => $this->is_overnight,
            'pickup_location'    => $this->pickup_location,
            'status'             => $this->status->value,
            'departure_reminded' => $this->departure_reminded,
            'return_reminded'    => $this->return_reminded,
            'notes'              => $this->notes,
            'created_at'         => $this->created_at->toISOString(),
        ];
    }
}
