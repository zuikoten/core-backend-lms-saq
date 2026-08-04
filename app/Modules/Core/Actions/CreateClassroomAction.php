<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Classroom;

class CreateClassroomAction
{
    public function execute(array $data): Classroom
    {
        return Classroom::query()->create([
            'name' => $data['name'],
            'capacity' => $data['capacity'] ?? null,
            'location' => $data['location'] ?? null,
        ]);
    }
}
