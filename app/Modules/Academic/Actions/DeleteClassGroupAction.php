<?php

namespace Modules\Academic\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ClassGroup;

class DeleteClassGroupAction
{
    /**
     * Rombel yang masih punya histori siswa (aktif ATAU sudah pindah
     * keluar) atau punya rapor tidak boleh dihapus — datanya harus tetap
     * bisa ditelusuri. DB constraint-nya sendiri sudah restrictOnDelete,
     * tapi guard di sini kasih pesan yang jelas ke staf, bukan error SQL
     * mentah.
     */
    public function execute(ClassGroup $classGroup): void
    {
        if ($classGroup->studentHistory()->exists()) {
            throw ValidationException::withMessages([
                'class_group' => 'Rombel ini masih punya histori siswa (aktif maupun yang sudah pindah). Tidak bisa dihapus.',
            ]);
        }

        if ($classGroup->reportCards()->exists()) {
            throw ValidationException::withMessages([
                'class_group' => 'Rombel ini masih punya data Rapor. Tidak bisa dihapus.',
            ]);
        }

        $classGroup->delete();
    }
}
