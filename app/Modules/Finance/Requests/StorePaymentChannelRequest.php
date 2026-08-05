<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_type' => ['required', Rule::in(['bank_transfer', 'virtual_account', 'e_wallet', 'cash'])],
            'name' => ['required', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:150'],
            'provider' => ['required', Rule::in(['manual', 'finpay'])],
            'provider_channel_code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
