<?php

namespace App\Http\Controllers\Api\V1\Admin\Blacklist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWhitelistIpRequest;
use App\Services\BlacklistService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class WhitelistIpController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BlacklistService $blacklistService
    ) {}

    public function index(): JsonResponse
    {
        $ips = $this->blacklistService->getWhitelistedIps();

        return $this->success(
            'Data IP whitelist berhasil diambil',
            $ips->map(fn ($ip) => [
                'id'         => $ip->id,
                'ip_address' => $ip->ip_address,
                'note'       => $ip->note,
                'is_active'  => $ip->is_active,
                'created_at' => $ip->created_at->toISOString(),
            ])
        );
    }

    public function store(StoreWhitelistIpRequest $request): JsonResponse
    {
        $ip = $this->blacklistService->whitelistIp(
            $request->validated('ip_address'),
            $request->validated('note', '')
        );

        return $this->success(
            'IP berhasil ditambahkan ke whitelist',
            [
                'id'         => $ip->id,
                'ip_address' => $ip->ip_address,
                'note'       => $ip->note,
                'is_active'  => $ip->is_active,
                'created_at' => $ip->created_at->toISOString(),
            ],
            201
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blacklistService->removeIpFromWhitelist($id);

        return $this->success('IP berhasil dihapus dari whitelist');
    }
}
