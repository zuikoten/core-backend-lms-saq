<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\GradeLevel;

class DeleteGradeLevelAction
{
    /**
     * Guard terhadap referensi dari class_groups (modul Academic) belum
     * ditambahkan di sini karena tabel itu belum dibuat — menyusul begitu
     * modul Academic mulai memakai grade_level_id.
     */
    public function execute(GradeLevel $gradeLevel): void
    {
        $gradeLevel->delete();
    }
}
