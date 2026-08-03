<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\Student;

class CreateStudentAction
{
    public function __construct(
        private FindOrCreateParentByPhoneAction $findOrCreateParent,
    ) {}

    /**
     * @return array{student: Student, parent_was_reused: bool}
     */
    public function execute(array $studentData, array $parentData): array
    {
        $parent = $this->findOrCreateParent->execute($parentData);

        $student = $parent->students()->create([
            ...$studentData,
            'status' => 'aktif',
        ]);

        return [
            'student' => $student,
            'parent_was_reused' => ! $parent->wasRecentlyCreated,
        ];
    }
}