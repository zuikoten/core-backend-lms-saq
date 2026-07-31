<?php

namespace Modules\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_years', 'year_name')->ignore($this->route('academicYear')),
            ],
        ];
    }
}
