<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Requests\Concerns\NormalizesPhoneNumber;

class RequestPhoneChangeOtpRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhoneNumberInput();
    }

    /**
     * Parent yang sudah setCredentials() (punya password) wajib konfirmasi
     * current_password dulu -- sama seperti pola staf, password jadi bukti
     * kepemilikan yang gak bergantung nomor lama sama sekali.
     *
     * Parent OTP-only (belum pernah setCredentials, password masih NULL)
     * sengaja TIDAK diwajibkan current_password -- itu satu-satunya jalur
     * yang mungkin buat dia, mewajibkan konfirmasi tambahan di sini cuma
     * bikin dia gak bisa pakai fitur ini sama sekali.
     */
    public function rules(): array
    {
        $hasPassword = $this->user()->password !== null;

        return [
            'current_password' => $hasPassword ? ['required', 'current_password:sanctum'] : ['prohibited'],
            'phone_number' => ['required', 'string', Rule::unique('users', 'phone_number')->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password yang kamu masukkan salah.',
            'current_password.prohibited' => 'Kamu belum set password, gak perlu isi field ini.',
            'phone_number.unique' => 'Nomor HP ini sudah dipakai akun lain.',
        ];
    }
}
