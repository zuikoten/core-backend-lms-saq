<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\ClassGroup;

class UpdateClassGroupAction
{
    /**
     * grade_level_id & academic_year_id SENGAJA tidak ikut diubah lewat
     * sini — memindahkan rombel ke jenjang/tahun ajaran lain itu operasi
     * berisiko (siswa & rapor yang sudah tertaut jadi tidak konsisten).
     * Kalau butuh rombel baru, buat baru.
     */
    public function execute(ClassGroup $classGroup, array $data): ClassGroup
    {
        $classGroup->update([
            'name' => $data['name'],
            'classroom_id' => $data['classroom_id'] ?? null,
        ]);

        return $classGroup;
    }
}
