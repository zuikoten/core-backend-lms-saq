<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Student\Models\Student;

class FindStudentsForInvoiceGenerationAction
{
    /**
     * Siswa "eligible" = berstatus aktif, punya minimal 1 tarif recurring
     * (SPP, Tabungan Wajib) yang dipetakan di tahun ajaran ini, DAN belum
     * punya invoice untuk periode bulan/tahun yang sama.
     *
     * Rombel TIDAK lagi jadi syarat — Finance tidak harus menunggu modul
     * Academic selesai. Kalau $classGroupId diisi, rombel hanya dipakai
     * sebagai filter ("tagih kelas ini saja").
     */
    public function execute(int $academicYearId, int $periodMonth, int $periodYear, ?int $classGroupId = null): array
    {
        $eligibleStudentIds = $this->candidateStudentIds($academicYearId, $classGroupId)
            ->diff($this->alreadyInvoicedStudentIds($academicYearId, $periodMonth, $periodYear));

        $recurringAmounts = $this->recurringAmounts($academicYearId, $eligibleStudentIds);

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

    /**
     * Siswa aktif yang belum diinvoice periode ini TAPI belum punya tarif
     * recurring sama sekali — tidak akan ikut digenerate. Ditampilkan sebagai
     * peringatan di UI supaya tidak ada anak yang terlewat tanpa disadari.
     */
    public function withoutTariff(int $academicYearId, int $periodMonth, int $periodYear, ?int $classGroupId = null): array
    {
        $candidates = $this->candidateStudentIds($academicYearId, $classGroupId)
            ->diff($this->alreadyInvoicedStudentIds($academicYearId, $periodMonth, $periodYear));

        $mappedIds = $this->recurringAmounts($academicYearId, $candidates)->keys();

        return Student::query()
            ->whereIn('id', $candidates->diff($mappedIds))
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn ($student) => [
                'id' => $student->id,
                'full_name' => $student->full_name,
            ])
            ->values()
            ->all();
    }

    private function candidateStudentIds(int $academicYearId, ?int $classGroupId): Collection
    {
        return Student::query()
            ->where('status', 'aktif')
            ->when($classGroupId, fn ($query) => $query->whereIn(
                'id',
                DB::table('class_group_students')
                    ->where('academic_year_id', $academicYearId)
                    ->whereNull('moved_out_at')
                    ->where('class_group_id', $classGroupId)
                    ->select('student_id')
            ))
            ->pluck('id');
    }

    private function alreadyInvoicedStudentIds(int $academicYearId, int $periodMonth, int $periodYear): Collection
    {
        return DB::table('invoices')
            ->where('academic_year_id', $academicYearId)
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->pluck('student_id');
    }

    private function recurringAmounts(int $academicYearId, Collection $studentIds): Collection
    {
        return DB::table('student_tariff_mappings')
            ->join('billing_tariffs', 'billing_tariffs.id', '=', 'student_tariff_mappings.billing_tariff_id')
            ->join('billing_types', 'billing_types.id', '=', 'student_tariff_mappings.billing_type_id')
            ->where('student_tariff_mappings.academic_year_id', $academicYearId)
            ->where('billing_types.is_recurring', true)
            ->whereIn('student_tariff_mappings.student_id', $studentIds)
            ->select('student_tariff_mappings.student_id', 'billing_tariffs.amount')
            ->get()
            ->groupBy('student_id');
    }
}
