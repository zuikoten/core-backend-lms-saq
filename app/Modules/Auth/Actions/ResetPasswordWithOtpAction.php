<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordWithOtpAction
{
    public function __construct(private readonly VerifyOtpAction $verifyOtpAction)
    {
    }

    /**
     * Khusus parent. Admin reset password lewat broker email bawaan Laravel
     * (lihat AdminPasswordResetController), BUKAN lewat action ini.
     */
    public function execute(string $phoneNumber, string $otpCode, string $newPassword): User
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! $user->hasRole('parent')) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor belum terdaftar, hubungi pihak sekolah.',
            ]);
        }

        $this->verifyOtpAction->execute('reset_password', $otpCode, user: $user);

        $user->update(['password' => Hash::make($newPassword)]);

        return $user;
    }
}
