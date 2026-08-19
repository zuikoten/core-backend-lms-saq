<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Rule bawaan Laravel 'current_password' otomatis cocokkan ke
            // password akun yang sedang login — gak perlu logic manual.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password lama yang kamu masukkan salah.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
