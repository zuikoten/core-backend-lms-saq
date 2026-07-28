<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Models\OtpCode;

class VerifyOtpAction
{
    /**
     * @param  string  $actionType  'activation' | 'login' | 'reset_password'
     * @param  string  $otpCode
     * @param  string|null  $phoneNumber  Wajib diisi untuk activation
     * @param  User|null  $user  Wajib diisi untuk login/reset_password
     */
    public function execute(string $actionType, string $otpCode, ?string $phoneNumber = null, ?User $user = null): OtpCode
    {
        $query = OtpCode::query()->where('action_type', $actionType);

        if ($actionType === 'activation') {
            $query->where('phone_number', $phoneNumber);
        } else {
            $query->where('user_id', $user?->id);
        }

        $otp = $query
            ->where('is_used', false)
            ->latest('created_at')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'otp_code' => 'Kode OTP salah.',
            ]);
        }

        if ($otp->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp_code' => 'Kode OTP sudah kedaluwarsa.',
            ]);
        }

        if (! hash_equals((string) $otp->otp_code, $otpCode)) {
            throw ValidationException::withMessages([
                'otp_code' => 'Kode OTP salah.',
            ]);
        }

        $otp->update(['is_used' => true]);

        return $otp;
    }
}
