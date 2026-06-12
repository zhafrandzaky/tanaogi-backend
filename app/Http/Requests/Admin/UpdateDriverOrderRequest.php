<?php

namespace App\Http\Requests\Admin;

use App\Enums\DriverOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => ['sometimes', 'nullable', 'uuid', 'exists:drivers,id'],
            'status'    => ['sometimes', Rule::enum(DriverOrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'driver_id.exists' => 'Driver tidak ditemukan',
            'status.enum'      => 'Status tidak valid',
        ];
    }
}
