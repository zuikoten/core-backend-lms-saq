<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class RegularUserSeeder extends Seeder
{
    public function run(): void
    {
        // ARRAY DATA USER YANG AKAN DIBUAT
        $users = [
            [
                'email' => 'headmaster@sekolah.test',
                'phone_number' => '082234567890',
                'password' => bcrypt('password123'),
                'is_active' => true,
                'role' => 'kepala_sekolah', 
            ],
            [
                'email' => 'staffadmin@sekolah.test',
                'phone_number' => '082234567891',
                'password' => bcrypt('password123'),
                'is_active' => true,
                'role' => 'staff_admin', 
            ],
            [
                'email' => 'guru@example.com',
                'phone_number' => '082234567892',
                'password' => bcrypt('password123'),
                'is_active' => true,
                'role' => 'guru', 
            ],
        ];

        
        foreach ($users as $userData) {
            
            $roleName = $userData['role'];
            unset($userData['role']);
            $user = User::create($userData);
            $user->assignRole($roleName);
        }
    }
}
