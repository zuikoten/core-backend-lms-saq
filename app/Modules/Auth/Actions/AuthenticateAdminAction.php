<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateAdminAction
{
    public function execute(string $email, string $password, bool $remember = false): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun tidak ditemukan atau sudah dinonaktifkan.',
            ]);
        }

        if (! $user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses admin.',
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        return $user;
    }
}