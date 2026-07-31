<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\AcademicYear;

class CreateAcademicYearAction
{
    /**
     * Tahun ajaran baru selalu dibuat dalam kondisi TIDAK aktif.
     * Mengaktifkan adalah keputusan terpisah & eksplisit lewat
     * ActivateAcademicYearAction, supaya tidak ada 2 tahun ajaran
     * aktif sekaligus secara tidak sengaja.
     */
    public function execute(string $yearName): AcademicYear
    {
        return AcademicYear::query()->create([
            'year_name' => $yearName,
            'is_active' => false,
        ]);
    }
}
