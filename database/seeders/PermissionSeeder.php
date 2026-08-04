<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'panel.access', 'guard_name' => 'web']);
        
        // Opsional / placeholder untuk nanti — modulnya (Finance, Academic, dst)
        // belum dibangun, jadi permission ini belum dipakai di mana pun. Aman
        // untuk di-seed lebih awal (tidak jadi "dead code"), tapi kalau mau ketat
        // ikutin prinsip "jangan bikin sesuatu buat modul yang belum ada",
        // tinggal comment/hapus baris-baris ini dulu sampai modulnya digarap.
        
        // Permission::firstOrCreate(['name' => 'finance.view', 'guard_name' => 'web']);
        // Permission::firstOrCreate(['name' => 'finance.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'academic.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'academic.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'core.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'core.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'student.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'student.manage', 'guard_name' => 'web']);
        // Permission::firstOrCreate(['name' => 'attendance.manage', 'guard_name' => 'web']);
        // Permission::firstOrCreate(['name' => 'exam.manage', 'guard_name' => 'web']);
        // Permission::firstOrCreate(['name' => 'settings.manage', 'guard_name' => 'web']); // kelola role & permission itu sendiri
    }
}
