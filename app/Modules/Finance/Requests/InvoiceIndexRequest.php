<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'status' => ['nullable', Rule::in(['unpaid', 'partial', 'paid', 'cancelled'])],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
        ];
    }
}