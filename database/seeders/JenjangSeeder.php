<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Jenjang;

class JenjangSeeder extends Seeder
{
    /**
     * Sistem ini sekarang fokus TK saja (sesuai HANDOFF.md), tapi skema
     * jenjang dirancang skalabel untuk multi-jenjang. Baris lain
     * (PAUD, SD, dst.) SENGAJA tidak ikut di-seed di sini — tambahkan
     * manual kalau sekolah memang butuh jenjang lain, jangan asal
     * di-uncomment tanpa ada kebutuhan nyata.
     */
    public function run(): void
    {
        Jenjang::firstOrCreate(
            ['name' => 'TK'],
            ['sort_order' => 1],
        );
    }
}