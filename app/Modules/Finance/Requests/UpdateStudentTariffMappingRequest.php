<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentTariffMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'billing_tariff_id' => ['required', 'integer', 'exists:billing_tariffs,id'],
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'required_with:approved_by', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required_with' => 'Catatan wajib diisi kalau ada persetujuan (approved_by) — jelaskan alasannya (mis. diskon tidak mampu, tarif ABK, dll).',
        ];
    }
}