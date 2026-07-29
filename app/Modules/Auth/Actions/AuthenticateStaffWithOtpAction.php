<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthenticateStaffWithOtpAction
{
    public function __construct(private readonly VerifyOtpAction $verifyOtpAction)
    {
    }

    /**
     * Mengembalikan User yang siap di-login manual di Controller lewat
     * Auth::guard('web')->login($user) — action ini sengaja tidak
     * melakukan login sesi sendiri, biar tetap konsisten "logika bisnis
     * di Action, efek samping request (session) di Controller".
     */
    public function execute(string $phoneNumber, string $otpCode): User
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! $user->can('panel.access')) {
            throw ValidationException::withMessages([
                'otp_code' => 'Akun tidak ditemukan atau tidak berwenang.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'otp_code' => 'Akun Anda tidak aktif, hubungi pemilik sistem.',
            ]);
        }

        $this->verifyOtpAction->execute('login', $otpCode, user: $user);

        return $user;
    }
}