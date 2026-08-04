<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id'),
                Rule::unique('report_cards', 'student_id')->where(fn ($query) => $query->where('semester_id', $this->input('semester_id'))),
            ],
            'semester_id' => ['required', 'integer', Rule::exists('semesters', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.unique' => 'Siswa ini sudah punya rapor untuk semester tersebut.',
        ];
    }
}
