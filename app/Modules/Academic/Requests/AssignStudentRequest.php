<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'class_group_id' => ['required', 'integer', Rule::exists('class_groups', 'id')],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
