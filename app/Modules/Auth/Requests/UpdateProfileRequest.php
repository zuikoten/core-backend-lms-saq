<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi implisit: hanya bisa edit profil sendiri, lihat ProfileController.
    }

    /**
     * username selalu dipaksa lowercase & di-trim sebelum divalidasi —
     * kolom ini bakal jadi slug URL profil publik, jadi "Budi-Santoso" dan
     * "budi-santoso" harus dianggap sama, bukan 2 nilai unik berbeda.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('username')) {
            $this->merge(['username' => strtolower(trim($this->string('username')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username cuma boleh huruf kecil, angka, dan strip (-), tanpa spasi.',
            'username.unique' => 'Username ini sudah dipakai orang lain.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format foto harus jpg, png, atau webp.',
            'avatar.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
