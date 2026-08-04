<?php

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\GradeLevel;

class DeleteGradeLevelAction
{
    /**
     * Guard terhadap class_groups ditambahkan lewat DB::table() (bukan
     * import Modules\Academic\Models\ClassGroup) SENGAJA — modul Core itu
     * fondasi, tidak boleh punya dependency balik ke modul yang
     * mengonsumsinya (Academic). Query by nama tabel tetap menegakkan
     * aturan bisnisnya tanpa bikin coupling antar-namespace modul.
     */
    public function execute(GradeLevel $gradeLevel): void
    {
        $isUsedByClassGroup = DB::table('class_groups')
            ->where('grade_level_id', $gradeLevel->id)
            ->exists();

        if ($isUsedByClassGroup) {
            throw ValidationException::withMessages([
                'grade_level' => 'Tingkat ini masih dipakai oleh Rombel di modul Academic. Hapus atau pindahkan rombel tersebut terlebih dahulu.',
            ]);
        }

        $gradeLevel->delete();
    }
}
