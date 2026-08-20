<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Requests\Concerns\NormalizesPhoneNumber;

class UpdatePhoneRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return true;
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
            'current_password' => ['required', 'current_password'],
            'phone_number' => ['required', 'string', Rule::unique('users', 'phone_number')->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password yang kamu masukkan salah.',
            'phone_number.unique' => 'Nomor HP ini sudah dipakai user lain.',
        ];
    }
}
