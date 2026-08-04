<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\GradeLevel;
use Modules\Core\Models\Jenjang;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $tk = Jenjang::query()->where('name', 'TK')->first();

        if (! $tk) {
            $this->command?->warn('Jenjang "TK" belum ada — jalankan JenjangSeeder dulu.');

            return;
        }

        $gradeLevels = [
            ['name' => 'TK-A', 'sort_order' => 1],
            ['name' => 'TK-B', 'sort_order' => 2],
        ];

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevel::firstOrCreate(
                ['jenjang_id' => $tk->id, 'name' => $gradeLevel['name']],
                ['sort_order' => $gradeLevel['sort_order']],
            );
        }
    }
}