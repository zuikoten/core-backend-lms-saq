<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\StudentTariffMapping;

class CreateStudentTariffMappingAction
{
    /**
     * academic_year_id & billing_type_id sengaja DIAMBIL dari BillingTariff
     * yang dipilih (bukan input form terpisah) — supaya kolom denormalisasi
     * ini selalu konsisten dengan tarif yang benar-benar dipetakan, tidak
     * mungkin staf pilih tarif tahun A tapi ke-input tahun ajaran B.
     */
    public function execute(array $data): StudentTariffMapping
    {
        $billingTariff = BillingTariff::findOrFail($data['billing_tariff_id']);

        $sudahDipetakan = DB::table('student_tariff_mappings')
            ->where('student_id', $data['student_id'])
            ->where('academic_year_id', $billingTariff->academic_year_id)
            ->where('billing_type_id', $billingTariff->billing_type_id)
            ->exists();

        if ($sudahDipetakan) {
            throw ValidationException::withMessages([
                'billing_tariff_id' => 'Siswa ini sudah punya tarif untuk jenis tagihan & tahun ajaran yang sama.',
            ]);
        }

        return StudentTariffMapping::create([
            'student_id' => $data['student_id'],
            'billing_tariff_id' => $billingTariff->id,
            'academic_year_id' => $billingTariff->academic_year_id,
            'billing_type_id' => $billingTariff->billing_type_id,
            'note' => $data['note'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
        ]);
    }
}