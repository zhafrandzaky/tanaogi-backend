<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'vehicle_type' => $this->vehicle_type,
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
