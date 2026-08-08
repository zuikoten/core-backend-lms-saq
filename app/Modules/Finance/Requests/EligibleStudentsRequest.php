<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EligibleStudentsRequest extends FormRequest
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
        ];
    }
}