<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['phone_number' => '6281234567890'], // ganti sesuai nomor admin sekolah
            [
                'email' => 'superadmin@sekolah.test',
                'password' => Hash::make('password'), // WAJIB diganti sebelum produksi
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole('superadmin')) {
            $admin->assignRole('superadmin');
        }
    }
}
