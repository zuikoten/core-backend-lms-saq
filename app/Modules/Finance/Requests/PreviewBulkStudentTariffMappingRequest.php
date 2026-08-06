<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewBulkStudentTariffMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_tariff_id' => ['required', 'integer', 'exists:billing_tariffs,id'],
            'filter_type' => ['required', Rule::in(['all', 'class_group'])],
            'class_group_id' => ['required_if:filter_type,class_group', 'nullable', 'integer', 'exists:class_groups,id'],
            'approved_by' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'required_with:approved_by', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_group_id.required_if' => 'Pilih rombel kalau filter yang dipakai adalah Rombel Tertentu.',
            'note.required_with' => 'Catatan wajib diisi kalau ada persetujuan (approved_by).',
        ];
    }
}