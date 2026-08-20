<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Requests\Concerns\NormalizesPhoneNumber;

class StoreUserRequest extends FormRequest
{
     use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return true; // Otorisasi dicek lewat middleware permission:user.manage di route.
    }

    // Normalisasi input nomor HP sebelum validasi, agar formatnya konsisten.
    // pakai trait NormalizesPhoneNumber
    protected function prepareForValidation(): void
    {
        $this->normalizePhoneNumberInput();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
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
