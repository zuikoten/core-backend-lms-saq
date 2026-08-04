<?php

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\Semester;

class DeleteSemesterAction
{
    public function execute(Semester $semester): void
    {
        if ($semester->is_active) {
            throw ValidationException::withMessages([
                'semester' => 'Semester yang sedang aktif tidak bisa dihapus. Aktifkan semester lain terlebih dahulu.',
            ]);
        }

        $isUsedByReportCard = DB::table('report_cards')
            ->where('semester_id', $semester->id)
            ->exists();

        if ($isUsedByReportCard) {
            throw ValidationException::withMessages([
                'semester' => 'Semester ini masih punya data Rapor di modul Academic. Tidak bisa dihapus.',
            ]);
        }

        $semester->delete();
    }
}
