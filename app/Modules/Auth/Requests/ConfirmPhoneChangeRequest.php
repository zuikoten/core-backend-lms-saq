<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPhoneChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp_code' => ['required', 'string'],
        ];
    }
}
