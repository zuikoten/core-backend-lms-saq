<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkStudentTariffMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_tariff_id' => ['required', 'integer', 'exists:billing_tariffs,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'required_with:approved_by', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Pilih minimal 1 siswa.',
            'note.required_with' => 'Catatan wajib diisi kalau ada persetujuan (approved_by).',
        ];
    }
}