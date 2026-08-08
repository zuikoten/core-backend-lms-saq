<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Student\Models\ParentProfile;
use Modules\Student\Models\Student;
use Illuminate\Support\Facades\Hash;

class ParentStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data orang tua yang akan digunakan
        $parentsData = [
            [
                'father_name' => 'Budi Santoso',
                'mother_name' => 'Siti Rahayu',
                'phone_number' => '081234567890',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'email' => 'budi.santoso@email.com',
            ],
            [
                'father_name' => 'Agus Wijaya',
                'mother_name' => 'Dewi Lestari',
                'phone_number' => '081234567891',
                'address' => 'Jl. Sudirman No. 45, Bandung',
                'email' => 'agus.wijaya@email.com',
            ],
            [
                'father_name' => 'Hendra Gunawan',
                'mother_name' => 'Rina Marlina',
                'phone_number' => '081234567892',
                'address' => 'Jl. Diponegoro No. 78, Surabaya',
                'email' => 'hendra.gunawan@email.com',
            ],
            [
                'father_name' => 'Slamet Riyadi',
                'mother_name' => 'Yuni Astuti',
                'phone_number' => '081234567893',
                'address' => 'Jl. Gatot Subroto No. 90, Yogyakarta',
                'email' => 'slamet.riyadi@email.com',
            ],
            [
                'father_name' => 'Bambang Prasetyo',
                'mother_name' => 'Indah Permata',
                'phone_number' => '081234567894',
                'address' => 'Jl. Pahlawan No. 56, Semarang',
                'email' => 'bambang.prasetyo@email.com',
            ],
            [
                'father_name' => 'Dedy Kusuma',
                'mother_name' => 'Putri Wulandari',
                'phone_number' => '081234567895',
                'address' => 'Jl. Ahmad Yani No. 34, Medan',
                'email' => 'dedy.kusuma@email.com',
            ],
            [
                'father_name' => 'Rudi Hartono',
                'mother_name' => 'Nina Safitri',
                'phone_number' => '081234567896',
                'address' => 'Jl. Thamrin No. 12, Makassar',
                'email' => 'rudi.hartono@email.com',
            ],
            [
                'father_name' => 'Eko Prasetyo',
                'mother_name' => 'Maya Sari',
                'phone_number' => '081234567897',
                'address' => 'Jl. Juanda No. 67, Palembang',
                'email' => 'eko.prasetyo@email.com',
            ],
            [
                'father_name' => 'Andi Malik',
                'mother_name' => 'Siti Nurhaliza',
                'phone_number' => '081234567898',
                'address' => 'Jl. Hasanuddin No. 89, Banjarmasin',
                'email' => 'andi.malik@email.com',
            ],
            [
                'father_name' => 'Fajar Nugroho',
                'mother_name' => 'Ratna Dewi',
                'phone_number' => '081234567899',
                'address' => 'Jl. Veteran No. 23, Bali',
                'email' => 'fajar.nugroho@email.com',
            ],
        ];

        // Simpan parent yang sudah dibuat untuk digunakan kembali
        $createdParents = [];

        // Buat User dan ParentProfile untuk setiap orang tua
        foreach ($parentsData as $parentData) {
            // Buat User terlebih dahulu
            $user = User::create([
                'email' => $parentData['email'],
                'phone_number' => $parentData['phone_number'],
                'password' => Hash::make('password123'), // password default
                'is_active' => true,
            ]);

            // Buat ParentProfile dengan user_id
            $parent = ParentProfile::create([
                'user_id' => $user->id,
                'father_name' => $parentData['father_name'],
                'mother_name' => $parentData['mother_name'],
                'phone_number' => $parentData['phone_number'],
                'address' => $parentData['address'],
            ]);

            $createdParents[] = $parent;
        }

        // Data siswa (20 siswa)
        $students = [
            // Anak dari Parent 1 (Budi Santoso) - 3 anak
            [
                'full_name' => 'Ahmad Fauzi',
                'nickname' => 'Ahmad',
                'gender' => 'L',
                'birth_date' => '2015-03-15',
                'status' => 'aktif',
                'nisn' => '1234567890',
                'parent_index' => 0,
            ],
            [
                'full_name' => 'Siti Fatimah',
                'nickname' => 'Siti',
                'gender' => 'P',
                'birth_date' => '2016-07-20',
                'status' => 'aktif',
                'nisn' => '1234567891',
                'parent_index' => 0,
            ],
            [
                'full_name' => 'Pandu Wirawan',
                'nickname' => 'Pandu',
                'gender' => 'L',
                'birth_date' => '2016-12-01',
                'status' => 'aktif',
                'nisn' => '1234567908',
                'parent_index' => 0,
            ],
            // Anak dari Parent 2 (Agus Wijaya) - 2 anak
            [
                'full_name' => 'Rizki Ramadhan',
                'nickname' => 'Rizki',
                'gender' => 'L',
                'birth_date' => '2015-11-10',
                'status' => 'aktif',
                'nisn' => '1234567892',
                'parent_index' => 1,
            ],
            [
                'full_name' => 'Dinda Ayu',
                'nickname' => 'Dinda',
                'gender' => 'P',
                'birth_date' => '2017-05-25',
                'status' => 'aktif',
                'nisn' => '1234567893',
                'parent_index' => 1,
            ],
            // Anak dari Parent 3 (Hendra Gunawan) - 2 anak
            [
                'full_name' => 'Bayu Prasetyo',
                'nickname' => 'Bayu',
                'gender' => 'L',
                'birth_date' => '2016-09-05',
                'status' => 'aktif',
                'nisn' => '1234567894',
                'parent_index' => 2,
            ],
            [
                'full_name' => 'Cahya Kirana',
                'nickname' => 'Cahya',
                'gender' => 'P',
                'birth_date' => '2015-12-30',
                'status' => 'lulus',
                'nisn' => '1234567895',
                'parent_index' => 2,
            ],
            // Anak dari Parent 4 (Slamet Riyadi) - 3 anak
            [
                'full_name' => 'Doni Setiawan',
                'nickname' => 'Doni',
                'gender' => 'L',
                'birth_date' => '2017-02-14',
                'status' => 'aktif',
                'nisn' => '1234567896',
                'parent_index' => 3,
            ],
            [
                'full_name' => 'Eka Putri',
                'nickname' => 'Eka',
                'gender' => 'P',
                'birth_date' => '2016-08-18',
                'status' => 'mutasi',
                'nisn' => '1234567897',
                'parent_index' => 3,
            ],
            [
                'full_name' => 'Qonita Zahra',
                'nickname' => 'Qonita',
                'gender' => 'P',
                'birth_date' => '2015-10-11',
                'status' => 'aktif',
                'nisn' => '1234567909',
                'parent_index' => 3,
            ],
            // Anak dari Parent 5 (Bambang Prasetyo) - 1 anak
            [
                'full_name' => 'Farhan Hidayat',
                'nickname' => 'Farhan',
                'gender' => 'L',
                'birth_date' => '2015-06-22',
                'status' => 'aktif',
                'nisn' => '1234567898',
                'parent_index' => 4,
            ],
            // Anak dari Parent 6 (Dedy Kusuma) - 2 anak
            [
                'full_name' => 'Gita Permata',
                'nickname' => 'Gita',
                'gender' => 'P',
                'birth_date' => '2016-04-12',
                'status' => 'aktif',
                'nisn' => '1234567899',
                'parent_index' => 5,
            ],
            [
                'full_name' => 'Hadi Prabowo',
                'nickname' => 'Hadi',
                'gender' => 'L',
                'birth_date' => '2017-10-08',
                'status' => 'aktif',
                'nisn' => '1234567900',
                'parent_index' => 5,
            ],
            // Anak dari Parent 7 (Rudi Hartono) - 1 anak
            [
                'full_name' => 'Indra Lesmana',
                'nickname' => 'Indra',
                'gender' => 'L',
                'birth_date' => '2015-01-19',
                'status' => 'lulus',
                'nisn' => '1234567901',
                'parent_index' => 6,
            ],
            // Anak dari Parent 8 (Eko Prasetyo) - 2 anak
            [
                'full_name' => 'Joko Susilo',
                'nickname' => 'Joko',
                'gender' => 'L',
                'birth_date' => '2016-07-30',
                'status' => 'aktif',
                'nisn' => '1234567902',
                'parent_index' => 7,
            ],
            [
                'full_name' => 'Kartika Sari',
                'nickname' => 'Kartika',
                'gender' => 'P',
                'birth_date' => '2017-03-27',
                'status' => 'aktif',
                'nisn' => '1234567903',
                'parent_index' => 7,
            ],
            // Anak dari Parent 9 (Andi Malik) - 2 anak
            [
                'full_name' => 'Lukman Hakim',
                'nickname' => 'Lukman',
                'gender' => 'L',
                'birth_date' => '2015-09-14',
                'status' => 'mutasi',
                'nisn' => '1234567904',
                'parent_index' => 8,
            ],
            [
                'full_name' => 'Maya Anggraini',
                'nickname' => 'Maya',
                'gender' => 'P',
                'birth_date' => '2016-11-23',
                'status' => 'aktif',
                'nisn' => '1234567905',
                'parent_index' => 8,
            ],
            // Anak dari Parent 10 (Fajar Nugroho) - 2 anak
            [
                'full_name' => 'Nanda Pratama',
                'nickname' => 'Nanda',
                'gender' => 'L',
                'birth_date' => '2017-06-09',
                'status' => 'aktif',
                'nisn' => '1234567906',
                'parent_index' => 9,
            ],
            [
                'full_name' => 'Oktavia Dewi',
                'nickname' => 'Oktavia',
                'gender' => 'P',
                'birth_date' => '2015-08-16',
                'status' => 'aktif',
                'nisn' => '1234567907',
                'parent_index' => 9,
            ],
        ];

        // Buat siswa berdasarkan data
        foreach ($students as $studentData) {
            $parentIndex = $studentData['parent_index'];
            unset($studentData['parent_index']);
            
            $studentData['parent_id'] = $createdParents[$parentIndex]->id;
            
            Student::create($studentData);
        }

        // Tampilkan informasi di console
        $this->command->info('✅ ParentStudentSeeder completed successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('  - ' . count($parentsData) . ' users');
        $this->command->info('  - ' . count($createdParents) . ' parent profiles');
        $this->command->info('  - ' . count($students) . ' students');
        
        // Tampilkan detail per parent
        $this->command->info("\n📋 Detail per parent:");
        foreach ($createdParents as $index => $parent) {
            $studentCount = Student::where('parent_id', $parent->id)->count();
            $user = User::find($parent->user_id);
            $this->command->info("  - {$parent->father_name} & {$parent->mother_name} (Email: {$user->email}): {$studentCount} anak");
        }
        
        // Tampilkan contoh credential untuk login
        $this->command->info("\n🔑 Contoh credential login:");
        $this->command->info("  Email: budi.santoso@email.com");
        $this->command->info("  Password: password123");
    }
}