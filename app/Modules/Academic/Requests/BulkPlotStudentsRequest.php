<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkPlotStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'distinct', Rule::exists('students', 'id')->where('status', 'aktif')],
            'class_group_id' => ['required', 'integer', Rule::exists('class_groups', 'id')],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Pilih minimal satu siswa.',
            'student_ids.min' => 'Pilih minimal satu siswa.',
            'student_ids.*.exists' => 'Ada siswa terpilih yang tidak ditemukan atau sudah tidak aktif.',
            'class_group_id.required' => 'Pilih rombel tujuan.',
        ];
    }
}
