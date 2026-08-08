<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Student\Models\Student;

class FindStudentsForInvoiceGenerationAction
{
    /**
     * Siswa "eligible" = aktif di rombel pada tahun ajaran ini, punya
     * minimal 1 tarif recurring (SPP) yang dipetakan, DAN belum punya
     * invoice untuk periode bulan/tahun yang sama — supaya generate
     * massal tidak dobel-invoice siswa yang sudah pernah digenerate.
     */
    public function execute(int $academicYearId, int $periodMonth, int $periodYear, ?int $classGroupId = null): array
    {
        $activeStudentIds = DB::table('class_group_students')
            ->where('academic_year_id', $academicYearId)
            ->whereNull('moved_out_at')
            ->when($classGroupId, fn ($query) => $query->where('class_group_id', $classGroupId))
            ->pluck('student_id');

        $alreadyInvoicedStudentIds = DB::table('invoices')
            ->where('academic_year_id', $academicYearId)
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->pluck('student_id');

        $eligibleStudentIds = $activeStudentIds->diff($alreadyInvoicedStudentIds);

        $recurringAmounts = DB::table('student_tariff_mappings')
            ->join('billing_tariffs', 'billing_tariffs.id', '=', 'student_tariff_mappings.billing_tariff_id')
            ->join('billing_types', 'billing_types.id', '=', 'student_tariff_mappings.billing_type_id')
            ->where('student_tariff_mappings.academic_year_id', $academicYearId)
            ->where('billing_types.is_recurring', true)
            ->whereIn('student_tariff_mappings.student_id', $eligibleStudentIds)
            ->select('student_tariff_mappings.student_id', 'billing_tariffs.amount')
            ->get()
            ->groupBy('student_id');

        return Student::query()
            ->whereIn('id', $recurringAmounts->keys())
            ->orderBy('full_name')
            ->get()
            ->map(fn ($student) => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'total_amount' => (float) $recurringAmounts[$student->id]->sum('amount'),
            ])
            ->values()
            ->all();
    }
}