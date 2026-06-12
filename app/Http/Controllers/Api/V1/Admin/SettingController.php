<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Services\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        $settings = $this->settingService->getAll();

        return $this->success(
            'Data pengaturan berhasil diambil',
            $settings->mapWithKeys(fn ($s) => [$s->key => $s->value])
        );
    }

    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $this->settingService->update($request->validated('settings'));

        $settings = $this->settingService->getAll();

        return $this->success(
            'Pengaturan berhasil diperbarui',
            $settings->mapWithKeys(fn ($s) => [$s->key => $s->value])
        );
    }
}
