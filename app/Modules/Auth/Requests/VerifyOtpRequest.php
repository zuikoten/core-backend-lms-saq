<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string'],
            'otp_code' => ['required', 'digits:6'],
            'action_type' => ['required', 'in:activation,login,reset_password'],
            // Diperlukan hanya kalau action_type == activation dan parent
            // sekaligus ingin set password saat itu juga (opsional).
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'new_password' => ['required_if:action_type,reset_password', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D/', '', (string) $this->input('phone_number'));

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        $this->merge(['phone_number' => $digits]);
    }
}
