<?php

namespace Modules\Academic\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ClassGroup;
use Modules\Academic\Models\ClassGroupStudent;

class TransferStudentAction
{
    /**
     * Pindah rombel = tutup baris histori yang sedang aktif (isi
     * moved_out_at) + buka baris baru untuk rombel tujuan, dalam 1
     * transaction. Rombel tujuan HARUS di tahun ajaran yang sama dengan
     * penempatan yang sedang aktif — pindah lintas tahun ajaran itu
     * proses "kenaikan kelas" yang beda, belum dibangun di sini.
     */
    public function execute(ClassGroupStudent $currentAssignment, ClassGroup $targetClassGroup, ?int $movedBy = null, ?string $note = null): ClassGroupStudent
    {
        if (! is_null($currentAssignment->moved_out_at)) {
            throw ValidationException::withMessages([
                'class_group' => 'Penempatan ini sudah tidak aktif (siswa sudah pindah sebelumnya).',
            ]);
        }

        if ($targetClassGroup->id === $currentAssignment->class_group_id) {
            throw ValidationException::withMessages([
                'class_group' => 'Rombel tujuan sama dengan rombel saat ini.',
            ]);
        }

        if ($targetClassGroup->academic_year_id !== $currentAssignment->academic_year_id) {
            throw ValidationException::withMessages([
                'class_group' => 'Rombel tujuan harus di tahun ajaran yang sama. Pindah lintas tahun ajaran belum didukung di sini.',
            ]);
        }

        return DB::transaction(function () use ($currentAssignment, $targetClassGroup, $movedBy, $note) {
            $currentAssignment->update(['moved_out_at' => now()->toDateString()]);

            return ClassGroupStudent::query()->create([
                'class_group_id' => $targetClassGroup->id,
                'student_id' => $currentAssignment->student_id,
                'academic_year_id' => $currentAssignment->academic_year_id,
                'moved_at' => now()->toDateString(),
                'moved_by' => $movedBy,
                'note' => $note,
            ]);
        });
    }
}
