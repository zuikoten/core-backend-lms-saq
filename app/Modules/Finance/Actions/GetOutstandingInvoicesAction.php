<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;

class GetOutstandingInvoicesAction
{
    /**
     * "Tunggakan" = invoice berstatus unpaid/partial. class_group_id
     * (opsional) discope lewat class_group_students aktif pada TAHUN
     * AJARAN invoice-nya (bukan tahun ajaran siswa saat ini) — supaya
     * laporan histori tahun ajaran lama tetap akurat.
     */
    public function execute(int $academicYearId, ?int $classGroupId): Collection
    {
        return Invoice::query()
            ->with(['student', 'payments'])
            ->where('academic_year_id', $academicYearId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($classGroupId, function ($query) use ($classGroupId, $academicYearId) {
                $studentIds = DB::table('class_group_students')
                    ->where('class_group_id', $classGroupId)
                    ->where('academic_year_id', $academicYearId)
                    ->whereNull('moved_out_at')
                    ->pluck('student_id');

                $query->whereIn('student_id', $studentIds);
            })
            ->get()
            ->map(fn ($invoice) => [
                'invoice' => $invoice,
                'total_terbayar' => $invoice->payments->sum('amount_paid'),
                'sisa_tagihan' => $invoice->total_amount - $invoice->payments->sum('amount_paid'),
            ])
            ->sortBy(fn ($row) => $row['invoice']->due_date)
            ->values();
    }
}