<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lihat catatan di StoreUserRequest soal normalisasi nomor HP — pola
     * yang sama diterapkan di sini supaya konsisten.
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
        $userId = $this->route('user')->id;

        return [
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => ['required', 'string', Rule::unique('users', 'phone_number')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Nomor HP wajib diisi.',
            'phone_number.unique' => 'Nomor HP ini sudah dipakai user lain.',
            'email.unique' => 'Email ini sudah dipakai user lain.',
        ];
    }
}
