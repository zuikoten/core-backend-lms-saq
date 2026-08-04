<?php

namespace Modules\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJenjangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('jenjang', 'name')->ignore($this->route('jenjang'))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
