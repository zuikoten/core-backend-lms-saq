<?php

namespace Modules\Auth\Actions;

use App\Models\User;

class UpdatePhoneAction
{
    /**
     * Kecocokan password lama & keunikan nomor baru sudah dicek di
     * UpdatePhoneRequest. Nomor HP dipisah dari UpdateProfileAction (name/
     * username/avatar) dengan alasan sama seperti UpdateEmailAction — nomor
     * ini dipakai jalur OTP login & OTP reset password, jadi sengaja diberi
     * lapisan konfirmasi ekstra, bukan diperlakukan sebagai field profil
     * biasa.
     */
    public function execute(User $user, string $newPhoneNumber): User
    {
        $user->update(['phone_number' => $newPhoneNumber]);

        return $user;
    }
}
