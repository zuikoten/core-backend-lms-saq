<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\Student;

class UpdateStudentAction
{
    /**
     * Hanya field milik Student sendiri. Data orang tua SENGAJA tidak
     * ikut di sini — lihat UpdateParentProfileAction.
     */
    public function execute(Student $student, array $data): Student
    {
        $student->update($data);

        return $student;
    }
}