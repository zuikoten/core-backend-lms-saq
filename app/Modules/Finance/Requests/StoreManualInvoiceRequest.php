<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'digits:4'],
            'due_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.billing_type_id' => ['required', 'integer', 'exists:billing_types,id'],
            'items.*.item_name' => ['required', 'string', 'max:150'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}