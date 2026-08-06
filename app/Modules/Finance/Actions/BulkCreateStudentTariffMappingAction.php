<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\StudentTariffMapping;

class BulkCreateStudentTariffMappingAction
{
    /**
     * Dipakai setelah staf konfirmasi daftar siswa di halaman preview.
     * Tetap dicek ulang per siswa (bukan percaya begitu saja hasil
     * preview) untuk menghindari race condition kalau ada pemetaan lain
     * masuk di antara preview dan submit — yang bentrok cukup di-skip,
     * bukan bikin seluruh proses gagal.
     *
     * @param  array<int>  $studentIds
     * @return array{created: int, skipped: int}
     */
    public function execute(BillingTariff $billingTariff, array $studentIds, ?string $note, ?int $approvedBy): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($studentIds as $studentId) {
            $sudahDipetakan = StudentTariffMapping::query()
                ->where('student_id', $studentId)
                ->where('academic_year_id', $billingTariff->academic_year_id)
                ->where('billing_type_id', $billingTariff->billing_type_id)
                ->exists();

            if ($sudahDipetakan) {
                $skipped++;

                continue;
            }

            StudentTariffMapping::create([
                'student_id' => $studentId,
                'billing_tariff_id' => $billingTariff->id,
                'academic_year_id' => $billingTariff->academic_year_id,
                'billing_type_id' => $billingTariff->billing_type_id,
                'note' => $note,
                'approved_by' => $approvedBy,
            ]);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
