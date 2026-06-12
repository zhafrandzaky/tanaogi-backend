<?php

namespace App\Http\Requests\Admin;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => ['sometimes', Rule::enum(VehicleType::class)],
            'name'          => ['sometimes', 'string', 'max:255'],
            'price_per_day' => ['sometimes', 'integer', 'min:0'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Nama kendaraan wajib diisi',
            'price_per_day.min'      => 'Harga per hari tidak boleh negatif',
        ];
    }
}
