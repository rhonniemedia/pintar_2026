<?php

namespace App\Http\Requests\Admin\Students;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStudentPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1024'], // Maksimal 1MB
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Pas foto wajib dipilih.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format gambar harus JPG, JPEG, atau PNG.',
            'photo.max'      => 'Ukuran file foto maksimal 1 MB.',
        ];
    }

    /**
     * Memaksa pengembalian format JSON saat validasi gagal,
     * agar bisa ditangkap dengan mudah oleh script Alpine.js / HTMX kita.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422));
    }
}
