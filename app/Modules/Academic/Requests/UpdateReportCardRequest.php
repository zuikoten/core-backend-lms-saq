<?php

namespace Modules\Academic\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'summary_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
