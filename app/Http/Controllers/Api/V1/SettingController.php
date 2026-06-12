<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function whatsapp(): JsonResponse
    {
        return $this->success(
            'Data pengaturan WhatsApp berhasil diambil',
            [
                'admin_whatsapp' => $this->settingService->getAdminWhatsapp(),
                'wa_template'    => $this->settingService->getWaTemplate(),
            ]
        );
    }
}
