<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccommodationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', Rule::enum(AccommodationType::class)],
            'price_per_night' => ['required', 'integer', 'min:0'],
            'address'        => ['required', 'string', 'max:500'],
            'destination_id' => ['required', 'uuid', 'exists:destinations,id'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'is_active'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama penginapan wajib diisi',
            'type.required'            => 'Tipe penginapan wajib dipilih',
            'price_per_night.required' => 'Harga per malam wajib diisi',
            'price_per_night.min'      => 'Harga per malam tidak boleh negatif',
            'address.required'         => 'Alamat wajib diisi',
            'destination_id.required'  => 'Destinasi wajib dipilih',
            'destination_id.exists'    => 'Destinasi tidak ditemukan',
        ];
    }
}
