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
        // Allow maintenance status endpoint to always be accessible
        if ($request->is('api/v1/maintenance/status')) {
            return $next($request);
        }

        // Admin with token can bypass maintenance
        if ($request->bearerToken() && $request->user()?->hasRole('admin')) {
            return $next($request);
        }

        // Secret bypass (matches Laravel's --secret option)
        $secret = config('app.maintenance_secret');
        if ($secret && $request->query('secret') === $secret) {
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
