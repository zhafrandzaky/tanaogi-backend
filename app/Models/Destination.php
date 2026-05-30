<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasUuids;

    protected $fillable = [
        'regency_id',
        'name',
        'slug',
        'description',
        'ticket_price',
        'facilities',
        'route_text',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ticket_price' => 'integer',
            'latitude'     => 'float',
            'longitude'    => 'float',
            'is_active'    => 'boolean',
            'facilities'   => 'array',
        ];
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
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
