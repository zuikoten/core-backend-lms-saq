<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\Student;

class DeleteStudentAction
{
    /**
     * Menghapus siswa TIDAK ikut menghapus baris `parents`-nya, meskipun
     * ini satu-satunya siswa dari orang tua tersebut — akun orang tua
     * (kalau sudah aktivasi) tetap harus bisa login, cuma daftar anaknya
     * jadi kosong. Guard terhadap data yang mereferensikan student_id dari
     * modul lain (invoice, tariff mapping) belum ditambahkan — menyusul
     * begitu modul Finance mulai memakainya.
     */
    public function execute(Student $student): void
    {
        $student->delete();
    }
}