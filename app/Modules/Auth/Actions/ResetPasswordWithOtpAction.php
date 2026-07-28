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
     * Dipakai staf mana pun yang punya permission panel.access (OTP WhatsApp,
     * alternatif dari broker email) maupun parent. Reset password lewat
     * broker email TETAP tersedia (lihat StaffPasswordResetController::
     * sendResetLinkEmail) — action ini hanya jalur alternatif OTP, bukan
     * pengganti.
     */
    public function execute(string $phoneNumber, string $otpCode, string $newPassword): User
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! ($user->hasRole('parent') || $user->can('panel.access'))) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor belum terdaftar, hubungi pihak sekolah.',
            ]);
        }

        $this->verifyOtpAction->execute('reset_password', $otpCode, user: $user);

        $user->update(['password' => Hash::make($newPassword)]);

        return $user;
    }
}
