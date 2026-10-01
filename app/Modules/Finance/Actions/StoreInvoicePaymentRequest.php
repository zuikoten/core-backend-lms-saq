<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoicePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_channel_id' => ['required', 'integer', 'exists:payment_channels,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'handover_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
