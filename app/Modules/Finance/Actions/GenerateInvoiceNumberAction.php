<?php

namespace Modules\Finance\Actions;

use Modules\Student\Models\Student;

class GenerateInvoiceNumberAction
{
    /**
     * Format: INV/{tahun}/{bulan}/{NISN}. Karena invoice sudah unik per
     * (student_id, academic_year_id, period_month, period_year) di level
     * bisnis, kombinasi ini otomatis unik juga — tidak perlu nomor urut
     * global terpisah. Kalau NISN belum diisi (nullable), fallback ke
     * "S{student_id}" supaya tetap unik & tidak ambigu dengan NISN asli.
     */
    public function execute(Student $student, int $periodYear, int $periodMonth): string
    {
        $identifier = $student->nisn ?: 'S'.$student->id;

        return sprintf('INV/%d/%02d/%s', $periodYear, $periodMonth, $identifier);
    }
}