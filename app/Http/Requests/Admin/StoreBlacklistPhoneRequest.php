<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlacklistPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'            => ['required', 'string', 'regex:/^62[0-9]{8,13}$/'],
            'reason'           => ['required', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'  => 'Nomor telepon wajib diisi',
            'phone.regex'     => 'Format nomor harus diawali 62 (contoh: 628123456789)',
            'reason.required' => 'Alasan ban wajib diisi',
        ];
    }
}
