<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlacklistPhoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'phone'          => $this->phone,
            'reason'         => $this->reason,
            'is_auto'        => $this->is_auto,
            'is_active'      => $this->is_active,
            'banned_at'      => $this->banned_at?->toISOString(),
            'banned_until'   => $this->banned_until?->toISOString(),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
