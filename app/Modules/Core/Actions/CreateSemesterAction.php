<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Semester;

class CreateSemesterAction
{
    /**
     * Sama seperti AcademicYear, semester baru selalu dibuat TIDAK aktif
     * — mengaktifkan adalah keputusan terpisah lewat ActivateSemesterAction.
     */
    public function execute(array $data): Semester
    {
        return Semester::query()->create([
            'academic_year_id' => $data['academic_year_id'],
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => false,
        ]);
    }
}
