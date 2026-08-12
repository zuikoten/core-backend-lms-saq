<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi dicek lewat middleware permission:user.manage di route.
    }

    /**
     * NOTE: normalisasi nomor HP di bawah ini pola generik (buang non-digit,
     * ganti awalan 0 jadi 62) mengikuti konvensi standar nomor HP di
     * README Auth. Karena User model punya mutator phoneNumber() yang juga
     * menormalisasi otomatis, cek dulu implementasi persisnya di model —
     * kalau sudah cukup lewat mutator, blok ini boleh disederhanakan/dibuang
     * biar tidak dobel logic.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone_number')) {
            $phone = preg_replace('/\D/', '', (string) $this->phone_number);

            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }

            $this->merge(['phone_number' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required', 'string', Rule::unique('users', 'phone_number')],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Nomor HP wajib diisi.',
            'phone_number.unique' => 'Nomor HP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ];
    }
}
