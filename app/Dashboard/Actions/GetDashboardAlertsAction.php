<?php

namespace App\Dashboard\Actions;

use Illuminate\Support\Facades\DB;

class GetDashboardAlertsAction
{
    /**
     * Hal-hal yang butuh perhatian staf: invoice yang jatuh tempo dalam
     * 7 hari ke depan tapi belum lunas, dan siswa aktif yang belum punya
     * pemetaan tarif SPP di tahun ajaran aktif (berisiko SPP-nya gak
     * pernah ke-generate lewat proses bulanan).
     */
    public function execute(): array
    {
        $academicYear = DB::table('academic_years')->where('is_active', true)->first();

        $invoiceJatuhTempo = DB::table('invoices')
            ->join('students', 'students.id', '=', 'invoices.student_id')
            ->whereIn('invoices.status', ['unpaid', 'partial'])
            ->whereNotNull('invoices.due_date')
            ->whereBetween('invoices.due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->select(
                'students.full_name as nama_siswa',
                'invoices.invoice_number',
                'invoices.due_date',
                'invoices.total_amount'
            )
            ->orderBy('invoices.due_date')
            ->limit(10)
            ->get();

        $jumlahSiswaTanpaTarif = $academicYear
            ? DB::table('students')
                ->where('students.status', 'aktif')
                ->whereNotIn('students.id', function ($query) use ($academicYear) {
                    $query->select('student_id')
                        ->from('student_tariff_mappings')
                        ->where('academic_year_id', $academicYear->id);
                })
                ->count()
            : 0;

        return [
            'invoice_jatuh_tempo' => $invoiceJatuhTempo->toArray(),
            'jumlah_siswa_tanpa_tarif' => $jumlahSiswaTanpaTarif,
        ];
    }
}
