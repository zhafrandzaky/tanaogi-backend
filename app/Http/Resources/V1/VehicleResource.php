<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'type'          => $this->type,
            'name'          => $this->name,
            'price_per_day' => $this->price_per_day,
            'description'   => $this->description,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at->toISOString(),
        ];
    }
}
