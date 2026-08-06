<?php
//seeder untuk tabel classrooms

namespace Database\Seeders;

use Modules\Core\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = [
            ['name' => 'Ruang A1', 'capacity' => 30, 'location' => 'Gedung Utama, Lantai 1'],
            ['name' => 'Ruang A2', 'capacity' => 25, 'location' => 'Gedung Utama, Lantai 1'],
            ['name' => 'Ruang B1', 'capacity' => 20, 'location' => 'Gedung Utama, Lantai 2'],
            ['name' => 'Ruang B2', 'capacity' => 15, 'location' => 'Gedung Utama, Lantai 2'],
            ['name' => 'Ruang C1', 'capacity' => 10, 'location' => 'Gedung Utama, Lantai 2'],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::firstOrCreate(['name' => $classroom['name']], $classroom);
        }
    }
}

