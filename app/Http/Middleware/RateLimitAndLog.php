<?php

namespace App\Http\Middleware;

use App\Services\BlacklistService;
use App\Services\RateLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAndLog
{
    public function __construct(
        private RateLimitService $rateLimitService,
        private BlacklistService $blacklistService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->rateLimitService->checkAndLogIp($request->ip(), $request->path());

        if ($this->blacklistService->isIpBlacklisted($request->ip())) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak request. Coba lagi nanti',
                'errors'  => null,
            ], 429);
        }

        return $next($request);
    }
}
