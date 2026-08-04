<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\GradeLevel;

class CreateGradeLevelAction
{
    public function execute(array $data): GradeLevel
    {
        return GradeLevel::query()->create([
            'jenjang_id' => $data['jenjang_id'],
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
