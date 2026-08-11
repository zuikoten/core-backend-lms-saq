<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyRecapReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
        ];
    }
}