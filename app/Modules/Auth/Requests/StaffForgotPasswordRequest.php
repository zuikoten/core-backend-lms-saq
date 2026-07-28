<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
