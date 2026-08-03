<?php

namespace Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'father_name' => ['nullable', 'string', 'max:150', 'required_without:mother_name'],
            'mother_name' => ['nullable', 'string', 'max:150', 'required_without:father_name'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'father_name.required_without' => 'Isi minimal salah satu: nama ayah atau nama ibu.',
            'mother_name.required_without' => 'Isi minimal salah satu: nama ayah atau nama ibu.',
        ];
    }
}