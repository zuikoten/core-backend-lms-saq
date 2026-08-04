<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'integer', Rule::exists('grade_levels', 'id')],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('class_groups', 'name')->where(fn ($query) => $query
                    ->where('grade_level_id', $this->input('grade_level_id'))
                    ->where('academic_year_id', $this->input('academic_year_id'))),
            ],
        ];
    }
}
