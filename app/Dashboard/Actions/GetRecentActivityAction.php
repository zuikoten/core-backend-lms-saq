<?php

namespace App\Dashboard\Actions;

use Illuminate\Support\Facades\DB;

class GetRecentActivityAction
{
    /**
     * Feed aktivitas terbaru gabungan dari 3 sumber (pembayaran masuk,
     * siswa baru terdaftar, perpindahan rombel). Digabung & diurutkan
     * ulang di level PHP (bukan UNION SQL) karena kolom waktu & bentuk
     * datanya beda-beda per sumber — lebih mudah dibaca & di-maintain.
     */
    public function execute(int $limit = 8): array
    {
        $pembayaran = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('students', 'students.id', '=', 'invoices.student_id')
            ->select(
                DB::raw("'pembayaran' as tipe"),
                'students.full_name as judul',
                'invoice_payments.amount_paid as nominal',
                'invoice_payments.paid_at as waktu'
            )
            ->orderByDesc('invoice_payments.paid_at')
            ->limit($limit)
            ->get();

        $siswaBaru = DB::table('students')
            ->select(
                DB::raw("'siswa_baru' as tipe"),
                'full_name as judul',
                DB::raw('NULL as nominal'),
                'created_at as waktu'
            )
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $pindahRombel = DB::table('class_group_students')
            ->join('students', 'students.id', '=', 'class_group_students.student_id')
            ->join('class_groups', 'class_groups.id', '=', 'class_group_students.class_group_id')
            ->select(
                DB::raw("'pindah_rombel' as tipe"),
                DB::raw("CONCAT(students.full_name, ' -> ', class_groups.name) as judul"),
                DB::raw('NULL as nominal'),
                'class_group_students.moved_at as waktu'
            )
            ->orderByDesc('class_group_students.moved_at')
            ->limit($limit)
            ->get();

        return $pembayaran
            ->concat($siswaBaru)
            ->concat($pindahRombel)
            ->sortByDesc('waktu')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
