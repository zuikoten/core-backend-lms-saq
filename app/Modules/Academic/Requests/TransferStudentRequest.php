<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_class_group_id' => ['required', 'integer', Rule::exists('class_groups', 'id')],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
