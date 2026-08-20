<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SetParentCredentialsAction
{
    /**
     * Endpoint ini cuma buat "lengkapi kredensial pertama kali" (parent
     * yang aktivasi via OTP, email & password-nya masih NULL). Kalau user
     * sudah punya email ATAU password (salah satu saja), request ditolak
     * — harus lewat jalur ganti email/password reguler (yang minta
     * konfirmasi current_password), bukan overwrite diam-diam tanpa bukti
     * kepemilikan akun sebelumnya.
     */
    public function execute(User $user, string $email, string $password): User
    {
        if ($user->email !== null || $user->password !== null) {
            throw ValidationException::withMessages([
                'email' => 'Kamu sudah punya email & password — gunakan menu ganti email/password, bukan lengkapi data awal.',
            ]);
        }

        $user->update([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        return $user;
    }
}
