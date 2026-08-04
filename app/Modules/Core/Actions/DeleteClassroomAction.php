<?php

namespace Modules\Core\Actions;

use Modules\Core\Models\Classroom;

class DeleteClassroomAction
{
    /**
     * Tidak perlu guard terhadap class_groups — FK-nya nullOnDelete
     * (rombel yang pakai ruang ini cuma jadi "belum ada ruang", tidak
     * ikut terhapus). Beda dari GradeLevel/AcademicYear/Semester yang
     * FK-nya restrictOnDelete karena data di baliknya jauh lebih kritis.
     */
    public function execute(Classroom $classroom): void
    {
        $classroom->delete();
    }
}
