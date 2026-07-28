<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthenticateParentWithOtpAction
{
    public function __construct(private readonly VerifyOtpAction $verifyOtpAction)
    {
    }

    /**
     * @return array{user: User, token: NewAccessToken}
     */
    public function execute(string $phoneNumber, string $otpCode): array
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! $user->hasRole('parent')) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor belum terdaftar, hubungi pihak sekolah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone_number' => 'Akun Anda tidak aktif, hubungi pihak sekolah.',
            ]);
        }

        $this->verifyOtpAction->execute('login', $otpCode, user: $user);

        return [
            'user' => $user,
            'token' => $user->createToken('parent-app'),
        ];
    }
}
