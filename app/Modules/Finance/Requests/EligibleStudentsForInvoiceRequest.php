<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EligibleStudentsForInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'digits:4'],
            'class_group_id' => ['nullable', 'integer', 'exists:class_groups,id'],
        ];
    }
}