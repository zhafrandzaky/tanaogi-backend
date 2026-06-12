<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    private const ALLOWED_KEYS = [
        'admin_whatsapp',
        'wa_template_driver_order',
        'reminder_hours_before_pickup',
        'max_requests_per_minute',
        'max_orders_per_phone',
        'orders_window_hours',
        'auto_ban_enabled',
        'auto_ban_duration_minutes',
        'is_maintenance',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'   => ['required', 'array', 'min:1'],
            'settings.*' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $settings = $this->input('settings', []);

            if (! is_array($settings)) {
                return;
            }

            $invalidKeys = array_diff(array_keys($settings), self::ALLOWED_KEYS);

            if (! empty($invalidKeys)) {
                $validator->errors()->add(
                    'settings',
                    'Key tidak valid: ' . implode(', ', $invalidKeys)
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Pengaturan wajib diisi.',
            'settings.array'    => 'Pengaturan harus berupa objek key-value.',
            'settings.min'      => 'Minimal satu pengaturan harus diisi.',
            'settings.*.string' => 'Nilai pengaturan harus berupa string.',
        ];
    }
}
