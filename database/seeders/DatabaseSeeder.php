<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // wajib pertama, karena AdminUserSeeder butuh role sudah ada
            PermissionSeeder::class,  // untuk setup permission panel.access, akses bebas bagi role manapun yang mau akses ke panel dashboard
            AcademicYearSeeder::class,
            BillingTypeSeeder::class,
            PaymentChannelSeeder::class,
            BillingTariffSeeder::class,
            AdminUserSeeder::class,   // wajib setelah RoleSeeder
        ]);
    }
}