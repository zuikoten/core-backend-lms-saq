<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthenticateParentWithPasswordAction
{
    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 60;

    /**
     * @return array{user: User, token: NewAccessToken}
     */
    public function execute(string $email, string $password): array
    {
        $throttleKey = 'login-parent:'.$email;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $user->hasRole('parent') || ! $user->password || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif, hubungi pihak sekolah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return [
            'user' => $user,
            'token' => $user->createToken('parent-app'),
        ];
    }
}