<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'driver_id'     => $this->resource['driver_id'],
            'driver_name'   => $this->resource['driver_name'],
            'month'         => $this->resource['month'],
            'year'          => $this->resource['year'],
            'blocked_dates' => $this->resource['blocked_dates'],
            'orders'        => $this->resource['orders'],
        ];
    }
}
