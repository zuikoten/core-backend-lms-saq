<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Dipakai di step KEDUA alur reset password OTP staff (setelah OTP sudah
 * diverifikasi di step pertama oleh VerifyOtpAction). Berbeda dari
 * ResetPasswordWithOtpAction yang menggabungkan verifikasi OTP + set
 * password dalam satu langkah (dipakai parent via API) — di sini OTP-nya
 * sudah pasti valid, jadi tinggal update password saja.
 */
class SetPasswordAfterOtpVerificationAction
{
    public function execute(string $phoneNumber, string $newPassword): User
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone_number' => 'Akun tidak ditemukan.',
            ]);
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return $user;
    }
}