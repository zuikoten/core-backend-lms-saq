<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComponentBreakdownReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'period_month' => ['nullable', 'integer', 'between:1,12'],
            'period_year' => ['nullable', 'integer', 'digits:4'],
        ];
    }
}