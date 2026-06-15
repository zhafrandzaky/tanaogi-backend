<?php

namespace App\Http\Controllers\Api\V1\Admin\Blacklist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlacklistIpRequest;
use App\Http\Resources\V1\BlacklistIpResource;
use App\Services\BlacklistService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BlacklistIpController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BlacklistService $blacklistService
    ) {}

    public function index(): JsonResponse
    {
        $ips = $this->blacklistService->getBlacklistedIps();

        return $this->success(
            'Data IP blacklist berhasil diambil',
            BlacklistIpResource::collection($ips)
        );
    }

    public function store(StoreBlacklistIpRequest $request): JsonResponse
    {
        $ip = $this->blacklistService->banIp(
            $request->validated('ip_address'),
            $request->validated('reason'),
            false,
            $request->validated('duration_minutes')
        );

        return $this->success(
            'IP berhasil dibanned',
            BlacklistIpResource::make($ip),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $ip = $this->blacklistService->findBlacklistIpById($id);

        return $this->success(
            'Detail IP blacklist berhasil diambil',
            BlacklistIpResource::make($ip)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blacklistService->deleteBlacklistIp($id);

        return $this->success('IP blacklist berhasil dihapus');
    }

    public function unban(string $id): JsonResponse
    {
        $this->blacklistService->unbanIp($id);

        return $this->success('IP berhasil di-unban');
    }
}
