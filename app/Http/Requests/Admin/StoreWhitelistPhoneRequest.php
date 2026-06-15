<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWhitelistPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^62[0-9]{8,13}$/'],
            'note'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.regex'    => 'Format nomor harus diawali 62 (contoh: 628123456789)',
        ];
    }
}
