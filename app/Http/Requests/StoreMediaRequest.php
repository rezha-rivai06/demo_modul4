<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    /**
     * Izinkan request masuk untuk diproses oleh controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk POST (store) dan PATCH/PUT (update).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PATCH') || $this->isMethod('PUT');
        $routeMedia = $this->route('media');
        $mediaId = $routeMedia instanceof Media ? $routeMedia->id : $routeMedia;
        $maxYear = (int) date('Y') + 5;

        return [
            'title' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('media', 'title')->ignore($mediaId),
            ],
            'type' => [
                $isUpdate ? 'sometimes' : 'required',
                'in:movie,anime',
            ],
            'genre' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'rating' => [
                $isUpdate ? 'sometimes' : 'required',
                'numeric',
                'min:0',
                'max:10',
            ],
            'release_year' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'min:1900',
                "max:{$maxYear}",
            ],
            'duration_minutes' => [
                $isUpdate ? 'sometimes' : 'required_if:type,movie',
                'nullable',
                'integer',
                'min:1',
            ],
            'director' => [
                $isUpdate ? 'sometimes' : 'required_if:type,movie',
                'nullable',
                'string',
                'max:255',
            ],
            'total_episodes' => [
                $isUpdate ? 'sometimes' : 'required_if:type,anime',
                'nullable',
                'integer',
                'min:1',
            ],
            'studio' => [
                $isUpdate ? 'sometimes' : 'required_if:type,anime',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Pesan error validasi yang jelas untuk respons 422.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi.',
            'title.unique' => 'Judul media sudah terdaftar dan tidak boleh duplikat.',
            'type.required' => 'Jenis media wajib diisi (movie atau anime).',
            'type.in' => 'Jenis media harus bernilai movie atau anime.',
            'genre.required' => 'Genre media wajib diisi.',
            'rating.required' => 'Rating media wajib diisi.',
            'rating.numeric' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 0.',
            'rating.max' => 'Rating maksimal 10.',
            'release_year.required' => 'Tahun rilis wajib diisi.',
            'release_year.integer' => 'Tahun rilis harus berupa angka bulat.',
            'duration_minutes.required_if' => 'Durasi (menit) wajib diisi untuk media bertipe movie.',
            'director.required_if' => 'Nama sutradara wajib diisi untuk media bertipe movie.',
            'total_episodes.required_if' => 'Total episode wajib diisi untuk media bertipe anime.',
            'studio.required_if' => 'Nama studio wajib diisi untuk media bertipe anime.',
        ];
    }
}
