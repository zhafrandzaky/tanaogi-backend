<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'regency_id'   => ['required', 'uuid', 'exists:regencies,id'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'ticket_price' => ['required', 'integer', 'min:0'],
            'facilities'   => ['nullable', 'array'],
            'facilities.*' => ['string'],
            'route_text'   => ['required', 'string'],
            'latitude'     => ['required', 'numeric'],
            'longitude'    => ['required', 'numeric'],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'regency_id.required'   => 'Kabupaten wajib dipilih',
            'regency_id.exists'     => 'Kabupaten tidak ditemukan',
            'name.required'         => 'Nama destinasi wajib diisi',
            'description.required'  => 'Deskripsi destinasi wajib diisi',
            'ticket_price.required' => 'Harga tiket wajib diisi',
            'ticket_price.min'      => 'Harga tiket tidak boleh negatif',
            'route_text.required'   => 'Rute perjalanan wajib diisi',
            'latitude.required'     => 'Koordinat latitude wajib diisi',
            'longitude.required'    => 'Koordinat longitude wajib diisi',
        ];
    }
}
