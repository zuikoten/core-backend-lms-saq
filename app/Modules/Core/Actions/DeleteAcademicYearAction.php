<?php

namespace Modules\Core\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Core\Models\AcademicYear;

class DeleteAcademicYearAction
{
    /**
     * Tahun ajaran yang sedang aktif tidak boleh dihapus begitu saja —
     * cegah kondisi sistem kehilangan referensi "tahun ajaran aktif"
     * secara tidak sengaja. Harus aktifkan tahun ajaran lain dulu.
     *
     * Catatan: guard terhadap data yang sudah terlanjur mereferensikan
     * academic_year_id (billing_tariffs, invoices, dst.) belum ditambahkan
     * di sini karena modul-modul tersebut belum digarap — akan menyusul.
     */
    public function execute(AcademicYear $academicYear): void
    {
        if ($academicYear->is_active) {
            throw ValidationException::withMessages([
                'academic_year' => 'Tahun ajaran yang sedang aktif tidak bisa dihapus. Aktifkan tahun ajaran lain terlebih dahulu.',
            ]);
        }

        $academicYear->delete();
    }
}
