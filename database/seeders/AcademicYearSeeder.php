<?php

namespace Database\Seeders;

use Modules\Core\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::firstOrCreate(
            ['year_name' => '2026/2027'],
            ['is_active' => true]
        );
    }
}
