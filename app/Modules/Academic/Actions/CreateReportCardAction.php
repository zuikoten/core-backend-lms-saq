<?php

namespace Modules\Academic\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ClassGroupStudent;
use Modules\Academic\Models\ReportCard;
use Modules\Core\Models\Semester;
use Modules\Student\Models\Student;

class CreateReportCardAction
{
    /**
     * class_group_id diambil otomatis dari penempatan AKTIF siswa di
     * tahun ajaran semester tersebut — bukan dipilih manual oleh staf.
     * Ini SNAPSHOT: kalau siswa pindah rombel setelah rapor dibuat,
     * rapor yang sudah ada tetap mengacu ke rombel saat dibuat, tidak
     * ikut berubah.
     */
    public function execute(Student $student, Semester $semester): ReportCard
    {
        $activeAssignment = ClassGroupStudent::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $semester->academic_year_id)
            ->active()
            ->first();

        if (! $activeAssignment) {
            throw ValidationException::withMessages([
                'student' => 'Siswa ini belum ditempatkan ke rombel manapun di tahun ajaran semester tersebut.',
            ]);
        }

        return ReportCard::query()->create([
            'student_id' => $student->id,
            'class_group_id' => $activeAssignment->class_group_id,
            'semester_id' => $semester->id,
            'status' => 'draft',
        ]);
    }
}
