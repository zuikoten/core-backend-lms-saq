<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthenticateParentWithPasswordAction
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    /**
     * @return array{user: User, token: NewAccessToken}
     */
    public function execute(string $phoneNumber, string $password): array
    {
        $throttleKey = 'login-parent:'.$phoneNumber;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'phone_number' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! $user->hasRole('parent') || ! $user->password || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'phone_number' => 'Nomor HP atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone_number' => 'Akun Anda tidak aktif, hubungi pihak sekolah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return [
            'user' => $user,
            'token' => $user->createToken('parent-app'),
        ];
    }
}
