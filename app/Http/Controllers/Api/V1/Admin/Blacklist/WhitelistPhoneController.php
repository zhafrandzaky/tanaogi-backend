<?php

namespace App\Http\Controllers\Api\V1\Admin\Blacklist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWhitelistPhoneRequest;
use App\Services\BlacklistService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class WhitelistPhoneController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BlacklistService $blacklistService
    ) {}

    public function index(): JsonResponse
    {
        $phones = $this->blacklistService->getWhitelistedPhones();

        return $this->success(
            'Data nomor whitelist berhasil diambil',
            $phones->map(fn ($phone) => [
                'id'         => $phone->id,
                'phone'      => $phone->phone,
                'note'       => $phone->note,
                'is_active'  => $phone->is_active,
                'created_at' => $phone->created_at->toISOString(),
            ])
        );
    }

    public function store(StoreWhitelistPhoneRequest $request): JsonResponse
    {
        $phone = $this->blacklistService->whitelistPhone(
            $request->validated('phone'),
            $request->validated('note', '')
        );

        return $this->success(
            'Nomor berhasil ditambahkan ke whitelist',
            [
                'id'         => $phone->id,
                'phone'      => $phone->phone,
                'note'       => $phone->note,
                'is_active'  => $phone->is_active,
                'created_at' => $phone->created_at->toISOString(),
            ],
            201
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->blacklistService->removePhoneFromWhitelist($id);

        return $this->success('Nomor berhasil dihapus dari whitelist');
    }
}
