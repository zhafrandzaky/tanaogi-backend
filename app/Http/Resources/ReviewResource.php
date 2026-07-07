<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Mengambil nama dari relasi tabel user
            'name' => $this->user->name ?? 'Pengguna',
            
            // Memberikan default avatar
            'avatar' => '#006b5e',
            
            'rating' => $this->rating,
            
            // Menerjemahkan 'comment' dari DB menjadi 'text' untuk Frontend
            'text' => $this->comment,
            
            // Mengubah format "2026-07-07 10:13:53" menjadi "July 2026"
            'date' => $this->created_at->format('F Y'),
        ];
    }
}