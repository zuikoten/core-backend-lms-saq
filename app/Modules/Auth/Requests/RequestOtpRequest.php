<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Standar penyimpanan nomor HP di sistem ini: 62xxxxxxxxxx (tanpa "+", tanpa
 * spasi/strip, awalan 0 diganti 62). Normalisasi dilakukan di sini supaya
 * Action tidak perlu peduli format input dari client.
 */
class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{8,11}$/'],
            'action_type' => ['required', 'in:activation,login,reset_password'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => $this->normalizePhoneNumber((string) $this->input('phone_number')),
        ]);
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D/', '', $phoneNumber);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
