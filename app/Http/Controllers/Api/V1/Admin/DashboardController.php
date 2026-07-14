<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Regency;
use App\Models\Accommodation;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function stats(): JsonResponse
    {
        $stats = [
            'destinations' => [
                'total'  => Destination::count(),
                'active' => Destination::active()->count(),
            ],
            'regencies' => [
                'total'  => Regency::count(),
                'active' => Regency::active()->count(),
            ],
            'accommodations' => [
                'total'  => Accommodation::count(),
                'active' => Accommodation::active()->count(),
            ],
            'vehicles' => [
                'total'  => Vehicle::count(),
                'active' => Vehicle::active()->count(),
            ],
        ];

        return $this->success('Dashboard stats retrieved successfully', $stats);
    }
}
