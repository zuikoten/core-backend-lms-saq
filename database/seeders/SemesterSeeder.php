<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\AcademicYear;
use Modules\Core\Models\Semester;

class SemesterSeeder extends Seeder
{
    /**
     * Semester butuh academic_year_id yang valid — seeder ini mengambil
     * tahun ajaran yang sedang AKTIF. Kalau belum ada tahun ajaran aktif
     * sama sekali (mis. fresh install sebelum staf sempat aktivasi lewat
     * panel), seeder ini di-skip dengan peringatan, bukan bikin data asal.
     *
     * Rentang tanggal & status is_active di bawah cuma ASUMSI kalender
     * umum sekolah Indonesia (Ganjil: Juli-Desember, Genap: Januari-Juni).
     * Sesuaikan manual dari panel kalau kalender sekolah beda.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()->where('is_active', true)->first();

        if (! $academicYear) {
            $this->command?->warn('Belum ada tahun ajaran aktif — SemesterSeeder di-skip. Aktifkan tahun ajaran dulu lewat panel, lalu jalankan ulang seeder ini.');

            return;
        }

        [$startYear, $endYear] = array_pad(explode('/', $academicYear->year_name), 2, null);
        $startYear = $startYear ?? now()->year;
        $endYear = $endYear ?? ((int) $startYear + 1);

        Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'Ganjil'],
            [
                'start_date' => "{$startYear}-07-01",
                'end_date' => "{$startYear}-12-31",
                'is_active' => true,
            ],
        );

        Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'Genap'],
            [
                'start_date' => "{$endYear}-01-01",
                'end_date' => "{$endYear}-06-30",
                'is_active' => false,
            ],
        );
    }
}