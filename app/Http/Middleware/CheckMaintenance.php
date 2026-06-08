<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    public function __construct(private MaintenanceService $maintenanceService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && $request->user()?->hasRole('admin')) {
            return $next($request);
        }

        if ($this->maintenanceService->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Website sedang dalam mode maintenance',
                'errors'  => null,
            ], 503);
        }

        return $next($request);
    }
}
