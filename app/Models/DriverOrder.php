<?php

namespace App\Models;

use App\Enums\DriverOrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'destination_id',
        'vehicle_id',
        'driver_id',
        'accommodation_id',
        'user_name',
        'user_phone',
        'departure_date',
        'return_date',
        'is_overnight',
        'pickup_location',
        'status',
        'departure_reminded',
        'return_reminded',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date'     => 'date',
            'return_date'        => 'date',
            'is_overnight'       => 'boolean',
            'departure_reminded' => 'boolean',
            'return_reminded'    => 'boolean',
            'status'             => DriverOrderStatus::class,
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}
