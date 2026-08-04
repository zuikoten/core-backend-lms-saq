<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\GradeLevel;

class UpdateGradeLevelAction
{
    public function execute(GradeLevel $gradeLevel, array $data): GradeLevel
    {
        $gradeLevel->update([
            'jenjang_id' => $data['jenjang_id'],
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $gradeLevel;
    }
}
