<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\BillingTariff;
use Modules\Student\Models\Student;

class FindEligibleStudentsForBulkTariffMappingAction
{
    /**
     * "Eligible" = siswa berstatus aktif yang BELUM punya pemetaan tarif untuk
     * kombinasi jenis tagihan + tahun ajaran yang sama — supaya bulk assign
     * tidak menimpa/duplikasi pemetaan manual yang sudah ada (mis. siswa yang
     * sudah dapat tarif khusus).
     *
     * Rombel TIDAK lagi jadi syarat (selaras dengan generate invoice massal):
     * kalau $classGroupId diisi, rombel aktif pada tahun ajaran tarif ini
     * hanya dipakai sebagai filter ("petakan kelas ini saja").
     */
    public function execute(BillingTariff $billingTariff, ?int $classGroupId = null): Collection
    {
        $alreadyMappedStudentIds = DB::table('student_tariff_mappings')
            ->where('academic_year_id', $billingTariff->academic_year_id)
            ->where('billing_type_id', $billingTariff->billing_type_id)
            ->pluck('student_id');

        return Student::query()
            ->where('status', 'aktif')
            ->when($classGroupId, fn ($query) => $query->whereIn(
                'id',
                DB::table('class_group_students')
                    ->where('academic_year_id', $billingTariff->academic_year_id)
                    ->whereNull('moved_out_at')
                    ->where('class_group_id', $classGroupId)
                    ->select('student_id')
            ))
            ->whereNotIn('id', $alreadyMappedStudentIds)
            ->orderBy('full_name')
            ->get();
    }
}
