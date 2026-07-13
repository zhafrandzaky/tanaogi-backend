<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'avatar' => $this->avatar,
            'role' => $this->getRoleNames()->first(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}