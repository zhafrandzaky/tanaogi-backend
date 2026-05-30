<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BlacklistIp extends Model
{
    use HasUuids;

    protected $fillable = [
        'ip_address',
        'reason',
        'is_auto',
        'banned_at',
        'banned_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_auto'      => 'boolean',
            'is_active'    => 'boolean',
            'banned_at'    => 'datetime',
            'banned_until' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
