<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutstandingInvoiceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'class_group_id' => ['nullable', 'integer', 'exists:class_groups,id'],
        ];
    }
}