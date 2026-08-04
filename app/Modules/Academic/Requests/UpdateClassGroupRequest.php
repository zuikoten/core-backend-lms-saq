<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $classGroup = $this->route('classGroup');

        return [
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('class_groups', 'name')
                    ->where(fn ($query) => $query
                        ->where('grade_level_id', $classGroup->grade_level_id)
                        ->where('academic_year_id', $classGroup->academic_year_id))
                    ->ignore($classGroup),
            ],
        ];
    }
}
