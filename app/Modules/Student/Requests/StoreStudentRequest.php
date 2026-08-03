<?php

namespace Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('parent_phone_number')) {
            $digits = preg_replace('/\D/', '', $this->input('parent_phone_number'));
            $normalized = str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;

            $this->merge(['parent_phone_number' => $normalized]);
        }
    }

    public function rules(): array
    {
        return [
            // Data siswa
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')],
            'full_name' => ['required', 'string', 'max:150'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['required', 'date'],

            // Data orang tua — dipakai FindOrCreateParentByPhoneAction
            'parent_phone_number' => ['required', 'string', 'min:9', 'max:15'],
            'parent_father_name' => ['nullable', 'string', 'max:150', 'required_without:parent_mother_name'],
            'parent_mother_name' => ['nullable', 'string', 'max:150', 'required_without:parent_father_name'],
            'parent_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_father_name.required_without' => 'Isi minimal salah satu: nama ayah atau nama ibu.',
            'parent_mother_name.required_without' => 'Isi minimal salah satu: nama ayah atau nama ibu.',
        ];
    }

    public function studentData(): array
    {
        return $this->only(['nisn', 'full_name', 'nickname', 'gender', 'birth_date']);
    }

    public function parentData(): array
    {
        return [
            'phone_number' => $this->validated('parent_phone_number'),
            'father_name' => $this->validated('parent_father_name'),
            'mother_name' => $this->validated('parent_mother_name'),
            'address' => $this->validated('parent_address'),
        ];
    }
}