<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadDestinationImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required'  => 'Minimal satu foto wajib diupload',
            'images.max'       => 'Maksimal 10 foto sekaligus',
            'images.*.image'   => 'File harus berupa gambar',
            'images.*.mimes'   => 'Format foto harus jpg, jpeg, png, atau webp',
            'images.*.max'     => 'Ukuran foto maksimal 2MB',
        ];
    }
}
