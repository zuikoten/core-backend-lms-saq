<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\ParentProfile;

class FindOrCreateParentByPhoneAction
{
    /**
     * Satu nomor HP = satu keluarga/wali. Kalau nomor HP yang diinput staf
     * sudah terdaftar di `parents` (kasus kakak-adik input terpisah),
     * REUSE baris itu — jangan bikin duplikat, supaya aktivasi akun orang
     * tua (modul Auth, cocokkan phone_number ke 1 baris `parents`) tidak
     * ambigu.
     *
     * Data father_name/mother_name/address yang diinput HANYA dipakai
     * kalau baris parent-nya baru dibuat. Kalau ternyata reuse baris yang
     * sudah ada, data lama TIDAK ditimpa di sini — perubahan data orang
     * tua yang sudah ada adalah tanggung jawab UpdateParentProfileAction,
     * bukan efek samping tambah siswa baru.
     */
    public function execute(array $data): ParentProfile
    {
        $phoneNumber = $this->normalize($data['phone_number']);

        return ParentProfile::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    /**
     * Duplikat sengaja dari mutator ParentProfile::phoneNumber() — query
     * pencarian (where('phone_number', ...)) tidak lewat mutator model,
     * jadi normalisasi harus dilakukan manual juga di sini sebelum dicari.
     * Sama seperti pola normalisasi nomor HP di modul Auth (dilakukan di
     * lebih dari 1 tempat secara sengaja).
     */
    private function normalize(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }
}