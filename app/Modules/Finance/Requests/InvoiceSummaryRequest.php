<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ];
    }
}