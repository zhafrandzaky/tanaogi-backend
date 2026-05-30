<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'name',
        'price_per_day',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'          => VehicleType::class,
            'price_per_day' => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    public function driverOrders(): HasMany
    {
        return $this->hasMany(DriverOrder::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
