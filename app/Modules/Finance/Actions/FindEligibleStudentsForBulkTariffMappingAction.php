<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\BillingTariff;
use Modules\Student\Models\Student;

class FindEligibleStudentsForBulkTariffMappingAction
{
    /**
     * "Eligible" = siswa yang aktif di rombel pada tahun ajaran tarif ini
     * (class_group_students.moved_out_at masih NULL), dan BELUM punya
     * pemetaan tarif untuk kombinasi jenis tagihan + tahun ajaran yang
     * sama — supaya bulk assign tidak menimpa/duplikasi pemetaan manual
     * yang sudah ada (mis. siswa yang sudah dapat tarif khusus).
     */
    public function execute(BillingTariff $billingTariff, ?int $classGroupId = null): Collection
    {
        $activeStudentIds = DB::table('class_group_students')
            ->where('academic_year_id', $billingTariff->academic_year_id)
            ->whereNull('moved_out_at')
            ->when($classGroupId, fn ($query) => $query->where('class_group_id', $classGroupId))
            ->pluck('student_id');

        $alreadyMappedStudentIds = DB::table('student_tariff_mappings')
            ->where('academic_year_id', $billingTariff->academic_year_id)
            ->where('billing_type_id', $billingTariff->billing_type_id)
            ->pluck('student_id');

        return Student::query()
            ->whereIn('id', $activeStudentIds)
            ->whereNotIn('id', $alreadyMappedStudentIds)
            ->orderBy('full_name')
            ->get();
    }
}