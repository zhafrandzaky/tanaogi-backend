<?php

namespace App\Http\Requests\Admin;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'phone'        => ['sometimes', 'string', 'regex:/^62[0-9]{7,15}$/', Rule::unique('drivers', 'phone')->ignore($this->route('driver'))],
            'vehicle_type' => ['sometimes', Rule::enum(VehicleType::class)],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama driver wajib diisi',
            'phone.regex'    => 'Nomor telepon harus diawali 62 (format: 628xxx)',
            'phone.unique'   => 'Nomor telepon sudah terdaftar',
        ];
    }
}
