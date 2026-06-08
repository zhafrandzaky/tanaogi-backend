<?php

namespace App\Http\Middleware;

use App\Services\BlacklistService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlacklistIp
{
    public function __construct(private BlacklistService $blacklistService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if ($this->blacklistService->isIpWhitelisted($ip)) {
            return $next($request);
        }

        if ($this->blacklistService->isIpBlacklisted($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak',
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}
