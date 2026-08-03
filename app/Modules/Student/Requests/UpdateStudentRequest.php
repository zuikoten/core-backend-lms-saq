<?php

namespace Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($this->route('student'))],
            'full_name' => ['required', 'string', 'max:150'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['aktif', 'mutasi', 'lulus'])],
        ];
    }
}