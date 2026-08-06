<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\StudentTariffMapping;

class DeleteStudentTariffMappingAction
{
    /**
     * Tidak ada guard pemakaian di sini — tidak ada tabel lain yang FK
     * langsung ke student_tariff_mappings.id. Generate invoice nanti baca
     * tarif aktif saat proses jalan, tidak menyimpan referensi balik ke
     * baris mapping ini. Aman dihapus kapan saja.
     */
    public function execute(StudentTariffMapping $studentTariffMapping): void
    {
        $studentTariffMapping->delete();
    }
}