<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'phone',
        'endpoint',
        'created_at',
    ];
}
