<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\AcademicYear;

class UpdateAcademicYearAction
{
    /**
     * Hanya mengurus `year_name`. Status aktif SENGAJA tidak ikut
     * diubah lewat sini — lihat ActivateAcademicYearAction.
     */
    public function execute(AcademicYear $academicYear, string $yearName): AcademicYear
    {
        $academicYear->update([
            'year_name' => $yearName,
        ]);

        return $academicYear;
    }
}
