<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCollectionRequest extends FormRequest
{
    /**
     * Izinkan request masuk untuk menambahkan media ke koleksi pengguna.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi payload media_id.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    /**
     * Pesan error validasi yang informatif.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'media_id.required' => 'ID media wajib diisi.',
            'media_id.integer' => 'ID media harus berupa angka.',
            'media_id.exists' => 'Media dengan ID tersebut tidak ditemukan.',
        ];
    }
}
