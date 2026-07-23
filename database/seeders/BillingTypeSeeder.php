<?php

namespace Database\Seeders;

use Modules\Finance\Models\BillingType;
use Illuminate\Database\Seeder;

class BillingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'SPP', 'is_recurring' => true],
            ['name' => 'Tabungan Wajib', 'is_recurring' => true],
            ['name' => 'Study Tour', 'is_recurring' => false],
            ['name' => 'Uang Buku', 'is_recurring' => false],
            ['name' => 'Uang Kegiatan', 'is_recurring' => false],
        ];

        foreach ($types as $type) {
            BillingType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
