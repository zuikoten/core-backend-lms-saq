<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction
{
    /**
     * Kecocokan password lama sudah dicek di UpdatePasswordRequest (rule
     * bawaan Laravel 'current_password') — Action ini murni tinggal ganti
     * ke password baru begitu validasi lolos.
     */
    public function execute(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);

        return $user;
    }
}
