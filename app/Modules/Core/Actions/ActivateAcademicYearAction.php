<?php

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\AcademicYear;

class ActivateAcademicYearAction
{
    /**
     * Hanya boleh ada SATU tahun ajaran aktif di satu waktu — dipakai
     * sebagai referensi default di seluruh sistem (Finance, Academic, dst).
     * Non-aktifkan semua tahun ajaran lain dulu, baru aktifkan yang dipilih,
     * dibungkus transaction supaya tidak ada kondisi "dua-duanya aktif"
     * kalau request-nya gagal di tengah jalan.
     */
    public function execute(AcademicYear $academicYear): AcademicYear
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()
                ->where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $academicYear->update(['is_active' => true]);
        });

        return $academicYear->fresh();
    }
}
