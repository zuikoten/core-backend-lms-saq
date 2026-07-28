<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffRequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{8,11}$/'],
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
