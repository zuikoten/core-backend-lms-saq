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

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', Rule::unique('users', 'phone_number')->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.unique' => 'Nomor HP ini sudah dipakai akun lain.',
        ];
    }
}
