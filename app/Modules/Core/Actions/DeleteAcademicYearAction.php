<?php

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\AcademicYear;

class DeleteAcademicYearAction
{
    /**
     * Tahun ajaran yang sedang aktif tidak boleh dihapus begitu saja —
     * cegah kondisi sistem kehilangan referensi "tahun ajaran aktif"
     * secara tidak sengaja. Harus aktifkan tahun ajaran lain dulu.
     *
     * Guard terhadap class_groups ditambahkan lewat DB::table() (bukan
     * import Model Academic) — lihat catatan yang sama di
     * DeleteGradeLevelAction soal kenapa Core tidak boleh depend ke
     * Academic.
     */
    public function execute(AcademicYear $academicYear): void
    {
        if ($academicYear->is_active) {
            throw ValidationException::withMessages([
                'academic_year' => 'Tahun ajaran yang sedang aktif tidak bisa dihapus. Aktifkan tahun ajaran lain terlebih dahulu.',
            ]);
        }

        $isUsedByClassGroup = DB::table('class_groups')
            ->where('academic_year_id', $academicYear->id)
            ->exists();

        if ($isUsedByClassGroup) {
            throw ValidationException::withMessages([
                'academic_year' => 'Tahun ajaran ini masih punya data Rombel di modul Academic. Tidak bisa dihapus.',
            ]);
        }

        $academicYear->delete();
    }
}
