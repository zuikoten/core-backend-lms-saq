<?php

namespace Modules\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::in(['Ganjil', 'Genap']),
                Rule::unique('semesters', 'name')
                    ->where(fn ($query) => $query->where('academic_year_id', $this->route('semester')->academic_year_id))
                    ->ignore($this->route('semester')),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
