<?php

namespace Modules\Core\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Core\Models\Jenjang;

class DeleteJenjangAction
{
    /**
     * Jenjang yang masih punya grade_levels tidak boleh dihapus langsung
     * — meski secara DB constraint-nya cascadeOnDelete (akan otomatis
     * ikut kehapus), kita SENGAJA cegah di level aplikasi supaya
     * penghapusan tingkat di bawahnya jadi keputusan eksplisit staf,
     * bukan efek samping tak terduga dari hapus jenjang.
     */
    public function execute(Jenjang $jenjang): void
    {
        if ($jenjang->gradeLevels()->exists()) {
            throw ValidationException::withMessages([
                'jenjang' => 'Jenjang ini masih memiliki tingkat/grade level. Hapus atau pindahkan tingkat tersebut terlebih dahulu.',
            ]);
        }

        $jenjang->delete();
    }
}
