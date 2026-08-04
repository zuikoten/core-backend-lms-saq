<?php

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Semester;

class ActivateSemesterAction
{
    /**
     * Hanya boleh ada SATU semester aktif di SELURUH sistem (bukan cuma
     * per tahun ajaran) — sama pola dengan AcademicYear::is_active, jadi
     * modul lain (Rapor, nanti Attendance/Learning) selalu punya 1 acuan
     * tunggal "semester yang sedang berjalan" tanpa perlu tahu tahun
     * ajaran mana dulu.
     */
    public function execute(Semester $semester): Semester
    {
        DB::transaction(function () use ($semester) {
            Semester::query()
                ->where('id', '!=', $semester->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $semester->update(['is_active' => true]);
        });

        return $semester->fresh();
    }
}
