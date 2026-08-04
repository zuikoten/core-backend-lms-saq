<?php

namespace Modules\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenjang_id' => ['required', 'integer', Rule::exists('jenjang', 'id')],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('grade_levels', 'name')->where(fn ($query) => $query->where('jenjang_id', $this->input('jenjang_id'))),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
