<?php

namespace Modules\Core\Actions;

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

        $semester->delete();
    }
}
