<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Classroom;

class UpdateClassroomAction
{
    public function execute(Classroom $classroom, array $data): Classroom
    {
        $classroom->update([
            'name' => $data['name'],
            'capacity' => $data['capacity'] ?? null,
            'location' => $data['location'] ?? null,
        ]);

        return $classroom;
    }
}
