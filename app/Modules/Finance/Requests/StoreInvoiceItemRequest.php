<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_type_id' => ['required', 'integer', 'exists:billing_types,id'],
            'item_name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}