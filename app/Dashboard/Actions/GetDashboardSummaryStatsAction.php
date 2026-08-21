<?php

namespace App\Dashboard\Actions;

use Illuminate\Support\Facades\DB;

class GetDashboardSummaryStatsAction
{
    /**
     * Agregasi angka ringkas untuk baris kartu statistik teratas dashboard.
     * Sengaja query lintas modul lewat DB::table() (bukan import Model),
     * karena Dashboard bersifat app-level & read-only — tidak ada alasan
     * mengikat diri ke relasi Eloquent milik modul lain yang bisa berubah
     * sewaktu-waktu tanpa Dashboard tahu.
     */
    public function execute(): array
    {
        $totalSiswaAktif = DB::table('students')
            ->where('status', 'aktif')
            ->count();

        $academicYear = DB::table('academic_years')
            ->where('is_active', true)
            ->first();

        $semester = DB::table('semesters')
            ->where('is_active', true)
            ->first();

        $jumlahRombelAktif = $academicYear
            ? DB::table('class_groups')
                ->where('academic_year_id', $academicYear->id)
                ->count()
            : 0;

        $pemasukanBulanIni = (int) DB::table('invoice_payments')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount_paid');

        // Tunggakan = total_amount invoice unpaid/partial dikurangi total
        // yang sudah dibayarkan ke invoice tsb (bukan cuma baca kolom
        // status, karena partial tetap punya sisa yang harus dihitung).
        $totalTunggakan = (int) DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partial'])
            ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->select(
                'invoices.id',
                'invoices.total_amount',
                DB::raw('COALESCE(SUM(invoice_payments.amount_paid), 0) as sudah_dibayar')
            )
            ->groupBy('invoices.id', 'invoices.total_amount')
            ->get()
            ->sum(fn ($invoice) => $invoice->total_amount - $invoice->sudah_dibayar);

        return [
            'total_siswa_aktif' => $totalSiswaAktif,
            'jumlah_rombel_aktif' => $jumlahRombelAktif,
            'tahun_ajaran_aktif' => $academicYear->year_name ?? '-',
            'semester_aktif' => $semester->name ?? '-',
            'pemasukan_bulan_ini' => $pemasukanBulanIni,
            'total_tunggakan' => $totalTunggakan,
        ];
    }
}
