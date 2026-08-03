<?php

namespace Modules\Student\Actions;

use Modules\Student\Models\ParentProfile;

class UpdateParentProfileAction
{
    /**
     * `phone_number` SENGAJA tidak bisa diubah lewat sini. Nomor itu
     * adalah identitas login/aktivasi orang tua di modul Auth — mengganti
     * nomor HP butuh alur tersendiri (re-verifikasi, dsb), bukan sekadar
     * "edit data orang tua" biasa. Belum dibangun; untuk sekarang ganti
     * nomor HP masih manual lewat Tinker kalau memang dibutuhkan.
     */
    public function execute(ParentProfile $parentProfile, array $data): ParentProfile
    {
        $parentProfile->update([
            'father_name' => $data['father_name'] ?? null,
            'mother_name' => $data['mother_name'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return $parentProfile;
    }
}