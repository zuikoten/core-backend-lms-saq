<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Requests\Concerns\NormalizesPhoneNumber;

class CheckOtpDeliveryStatusRequest extends FormRequest
{
    use NormalizesPhoneNumber;

    public function authorize(): bool
    {
        return true; // Publik -- lihat catatan di OtpDeliveryStatusApiController.
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhoneNumberInput();
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string'],
            'action_type' => ['required', Rule::in(['activation', 'login', 'reset_password', 'change_phone'])],
        ];
    }
}
