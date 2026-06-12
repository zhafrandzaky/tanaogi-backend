<?php

namespace App\Http\Requests\Admin;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'regex:/^62[0-9]{7,15}$/', 'unique:drivers,phone'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama driver wajib diisi',
            'phone.required'        => 'Nomor telepon wajib diisi',
            'phone.regex'           => 'Nomor telepon harus diawali 62 (format: 628xxx)',
            'phone.unique'          => 'Nomor telepon sudah terdaftar',
            'vehicle_type.required' => 'Tipe kendaraan wajib dipilih',
        ];
    }
}
