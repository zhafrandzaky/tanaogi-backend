<?php

namespace App\Http\Controllers\Api\V1\Admin\Blacklist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlacklistPhoneRequest;
use App\Http\Resources\V1\BlacklistPhoneResource;
use App\Services\BlacklistService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BlacklistPhoneController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BlacklistService $blacklistService
    ) {}

    public function index(): JsonResponse
    {
        $phones = $this->blacklistService->getBlacklistedPhones();

        return $this->success(
            'Data nomor blacklist berhasil diambil',
            BlacklistPhoneResource::collection($phones)
        );
    }

    public function store(StoreBlacklistPhoneRequest $request): JsonResponse
    {
        $phone = $this->blacklistService->banPhone(
            $request->validated('phone'),
            $request->validated('reason'),
            false,
            $request->validated('duration_minutes')
        );

        return $this->success(
            'Nomor berhasil dibanned',
            BlacklistPhoneResource::make($phone),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $phone = $this->blacklistService->findBlacklistPhoneById($id);

        return $this->success(
            'Detail nomor blacklist berhasil diambil',
            BlacklistPhoneResource::make($phone)
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blacklistService->deleteBlacklistPhone($id);

        return $this->success('Nomor blacklist berhasil dihapus');
    }

    public function unban(string $id): JsonResponse
    {
        $this->blacklistService->unbanPhone($id);

        return $this->success('Nomor berhasil di-unban');
    }
}
