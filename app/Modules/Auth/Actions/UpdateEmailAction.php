<?php

namespace Modules\Auth\Actions;

use App\Models\User;

class UpdateEmailAction
{
    /**
     * Kecocokan password lama & keunikan email baru sudah dicek di
     * UpdateEmailRequest — dipisah dari UpdateProfileAction (name/username/
     * avatar) karena email dipakai jalur reset password via email, jadi
     * sengaja diberi lapisan konfirmasi ekstra yang sama seperti ganti
     * password, bukan diperlakukan sebagai field profil biasa.
     */
    public function execute(User $user, string $newEmail): User
    {
        $user->update(['email' => $newEmail]);

        return $user;
    }
}
