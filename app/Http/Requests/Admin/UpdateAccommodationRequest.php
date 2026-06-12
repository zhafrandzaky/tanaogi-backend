<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccommodationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes', 'string', 'max:255'],
            'type'            => ['sometimes', Rule::enum(AccommodationType::class)],
            'price_per_night' => ['sometimes', 'integer', 'min:0'],
            'address'         => ['sometimes', 'string', 'max:500'],
            'destination_id'  => ['sometimes', 'uuid', 'exists:destinations,id'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price_per_night.min'     => 'Harga per malam tidak boleh negatif',
            'destination_id.exists'   => 'Destinasi tidak ditemukan',
        ];
    }
}
