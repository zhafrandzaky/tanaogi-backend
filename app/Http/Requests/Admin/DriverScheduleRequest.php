<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DriverScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year'  => ['required', 'integer', 'min:2024', 'max:2030'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'Bulan wajib diisi',
            'month.min'      => 'Bulan harus antara 1-12',
            'month.max'      => 'Bulan harus antara 1-12',
            'year.required'  => 'Tahun wajib diisi',
            'year.min'       => 'Tahun tidak valid',
            'year.max'       => 'Tahun tidak valid',
        ];
    }
}
