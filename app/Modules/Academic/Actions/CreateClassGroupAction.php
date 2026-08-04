<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\ClassGroup;

class CreateClassGroupAction
{
    public function execute(array $data): ClassGroup
    {
        return ClassGroup::query()->create([
            'grade_level_id' => $data['grade_level_id'],
            'academic_year_id' => $data['academic_year_id'],
            'classroom_id' => $data['classroom_id'] ?? null,
            'name' => $data['name'],
            // homeroom_teacher_id sengaja tidak diisi lewat sini — belum
            // ada modul Teacher untuk memilihnya. Diisi manual lewat
            // Tinker/DB kalau memang perlu, atau nyusul begitu Teacher ada.
        ]);
    }
}
