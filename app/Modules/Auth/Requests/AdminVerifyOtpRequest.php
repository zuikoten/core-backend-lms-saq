<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminVerifyOtpRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
