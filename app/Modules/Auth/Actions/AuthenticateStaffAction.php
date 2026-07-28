<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticateStaffAction
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function execute(string $email, string $password, bool $remember = false): void
    {
        $throttleKey = 'login-staff:'.strtolower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = \App\Models\User::query()->where('email', $email)->first();

        if (! $user || ! $user->can('panel.access')) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif, hubungi pemilik sistem.',
            ]);
        }

        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $remember)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);
    }
}
