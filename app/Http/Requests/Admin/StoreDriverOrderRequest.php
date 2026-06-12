<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_id'   => ['required', 'uuid', 'exists:destinations,id'],
            'vehicle_id'       => ['required', 'uuid', 'exists:vehicles,id'],
            'accommodation_id' => ['nullable', 'uuid', 'exists:accommodations,id'],
            'user_name'        => ['required', 'string', 'max:255'],
            'user_phone'       => ['required', 'string', 'regex:/^62[0-9]{7,15}$/'],
            'departure_date'   => ['required', 'date', 'date_format:Y-m-d'],
            'return_date'      => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:departure_date'],
            'is_overnight'     => ['boolean'],
            'pickup_location'  => ['required', 'string'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_id.required'   => 'Destinasi wajib dipilih',
            'destination_id.exists'     => 'Destinasi tidak ditemukan',
            'vehicle_id.required'       => 'Kendaraan wajib dipilih',
            'vehicle_id.exists'         => 'Kendaraan tidak ditemukan',
            'accommodation_id.exists'   => 'Penginapan tidak ditemukan',
            'user_name.required'        => 'Nama pemesan wajib diisi',
            'user_phone.required'       => 'Nomor telepon wajib diisi',
            'user_phone.regex'          => 'Nomor telepon harus diawali 62 (format: 628xxx)',
            'departure_date.required'   => 'Tanggal berangkat wajib diisi',
            'departure_date.date_format' => 'Format tanggal berangkat harus YYYY-MM-DD',
            'return_date.required'      => 'Tanggal pulang wajib diisi',
            'return_date.date_format'   => 'Format tanggal pulang harus YYYY-MM-DD',
            'return_date.after_or_equal' => 'Tanggal pulang harus sama atau setelah tanggal berangkat',
            'pickup_location.required'  => 'Lokasi penjemputan wajib diisi',
        ];
    }
}
