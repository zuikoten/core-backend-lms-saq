<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;

class GetInvoiceDetailAction
{
    /**
     * Info kelas (buat tampilan "TK B — Kelas Matahari") diambil lewat
     * DB::table(), bukan import Model Academic — Finance tidak boleh
     * nge-couple ke Model modul lain, cukup query by nama tabel.
     */
    public function execute(Invoice $invoice): array
    {
        $invoice->load(['items', 'payments', 'student']);

        $classGroup = DB::table('class_group_students')
            ->join('class_groups', 'class_groups.id', '=', 'class_group_students.class_group_id')
            ->join('grade_levels', 'grade_levels.id', '=', 'class_groups.grade_level_id')
            ->where('class_group_students.student_id', $invoice->student_id)
            ->where('class_group_students.academic_year_id', $invoice->academic_year_id)
            ->whereNull('class_group_students.moved_out_at')
            ->select('grade_levels.name as grade_level_name', 'class_groups.name as class_group_name')
            ->first();

        return [
            'invoice' => $invoice,
            'grade_level_name' => $classGroup->grade_level_name ?? null,
            'class_group_name' => $classGroup->class_group_name ?? null,
        ];
    }
}