<?php

namespace App\Http\Requests\Admin;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => ['required', Rule::enum(VehicleType::class)],
            'name'          => ['required', 'string', 'max:255'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'          => 'Tipe kendaraan wajib dipilih',
            'name.required'          => 'Nama kendaraan wajib diisi',
            'price_per_day.required' => 'Harga per hari wajib diisi',
            'price_per_day.min'      => 'Harga per hari tidak boleh negatif',
        ];
    }
}
