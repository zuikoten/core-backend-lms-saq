<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // wajib pertama, karena AdminUserSeeder butuh role sudah ada
            AcademicYearSeeder::class,
            BillingTypeSeeder::class,
            PaymentChannelSeeder::class,
            AdminUserSeeder::class,   // wajib setelah RoleSeeder
        ]);
    }
}