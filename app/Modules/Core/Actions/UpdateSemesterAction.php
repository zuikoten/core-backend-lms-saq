<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Semester;

class UpdateSemesterAction
{
    /**
     * Status aktif SENGAJA tidak ikut diubah lewat sini — lihat
     * ActivateSemesterAction. academic_year_id juga tidak diubah lewat
     * edit biasa — memindahkan semester ke tahun ajaran lain adalah
     * operasi berisiko (mempengaruhi rapor & data lain yang sudah
     * tertaut), belum ada use case nyata untuk itu sekarang.
     */
    public function execute(Semester $semester, array $data): Semester
    {
        $semester->update([
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        return $semester;
    }
}
