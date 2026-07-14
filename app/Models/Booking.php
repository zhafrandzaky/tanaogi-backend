<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'destination_id',
        'destination_slug',
        'visit_date',
        'pax_count',
        'has_driver',
        'driver_package',
        'driver_price',
        'driver_id',
        'include_hotel',
        'selected_hotel',
        'hotel_price',
        'accommodation_id',
        'total_amount_web',
        'entrance_ticket_fee_onsite',
        'payment_status',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'customer_name',
        'customer_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date'                 => 'date',
            'pax_count'                  => 'integer',
            'has_driver'                 => 'boolean',
            'driver_price'               => 'float',
            'include_hotel'              => 'boolean',
            'hotel_price'                => 'float',
            'total_amount_web'           => 'float',
            'entrance_ticket_fee_onsite' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
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
