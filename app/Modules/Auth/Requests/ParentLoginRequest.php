<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string'],
            'password' => ['required', 'string'],
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
