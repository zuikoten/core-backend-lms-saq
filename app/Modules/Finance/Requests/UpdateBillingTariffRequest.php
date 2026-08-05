<?php

namespace Modules\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillingTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_type_id' => ['required', 'integer', 'exists:billing_types,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'tariff_name' => [
                'required', 'string', 'max:150',
                Rule::unique('billing_tariffs', 'tariff_name')
                    ->where(fn ($query) => $query
                        ->where('billing_type_id', $this->input('billing_type_id'))
                        ->where('academic_year_id', $this->input('academic_year_id')))
                    ->ignore($this->route('billingTariff')),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tariff_name.unique' => 'Sudah ada tarif dengan nama ini untuk jenis tagihan & tahun ajaran yang sama.',
        ];
    }
}