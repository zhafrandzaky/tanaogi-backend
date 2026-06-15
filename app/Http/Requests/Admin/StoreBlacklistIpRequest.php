<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlacklistIpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ip_address'       => ['required', 'ip'],
            'reason'           => ['required', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'ip_address.required' => 'IP address wajib diisi',
            'ip_address.ip'       => 'Format IP address tidak valid',
            'reason.required'     => 'Alasan ban wajib diisi',
        ];
    }
}
