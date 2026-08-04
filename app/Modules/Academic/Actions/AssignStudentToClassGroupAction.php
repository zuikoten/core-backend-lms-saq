<?php

namespace Modules\Academic\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ClassGroup;
use Modules\Academic\Models\ClassGroupStudent;
use Modules\Student\Models\Student;

class AssignStudentToClassGroupAction
{
    /**
     * Penempatan PERTAMA KALI siswa ke rombel di suatu tahun ajaran.
     * Kalau siswa sudah punya penempatan aktif di tahun ajaran yang sama
     * (class_group->academic_year_id), tolak — harus lewat
     * TransferStudentAction, bukan assign baru, supaya histori tetap
     * tercatat rapi (tidak ada 2 baris aktif tanpa hubungan).
     */
    public function execute(Student $student, ClassGroup $classGroup, ?int $movedBy = null, ?string $note = null): ClassGroupStudent
    {
        $alreadyActive = ClassGroupStudent::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->active()
            ->exists();

        if ($alreadyActive) {
            throw ValidationException::withMessages([
                'student' => 'Siswa ini sudah punya rombel aktif di tahun ajaran tersebut. Gunakan fitur "Pindah Rombel", bukan tambah baru.',
            ]);
        }

        return ClassGroupStudent::query()->create([
            'class_group_id' => $classGroup->id,
            'student_id' => $student->id,
            'academic_year_id' => $classGroup->academic_year_id,
            'moved_at' => now()->toDateString(),
            'moved_by' => $movedBy,
            'note' => $note,
        ]);
    }
}
