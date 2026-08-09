<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Notifications\SendOtpWhatsappNotification;

class GenerateOtpAction
{
    private const RATE_LIMIT_SECONDS = 60;

    private const EXPIRES_IN_MINUTES = 5;

    /**
     * @param  string  $actionType  'activation' | 'login' | 'reset_password'
     * @param  string  $phoneNumber  Nomor sudah dinormalisasi oleh FormRequest (format 62xxxxxxxxxx)
     * @param  User|null  $user  Wajib diisi untuk login/reset_password, WAJIB null untuk activation
     */
    public function execute(string $actionType, string $phoneNumber, ?User $user = null): OtpCode
    {
        $this->validateContext($actionType, $user);
        $this->guardRateLimit($actionType, $phoneNumber, $user);

        $plainOtp = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'user_id' => $user?->id,
            'phone_number' => $phoneNumber,
            'otp_code' => $plainOtp,
            'action_type' => $actionType,
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
            'is_used' => false,
        ]);

        // Notifiable sederhana: cukup User (kalau ada) atau objek anonim
        // dengan routeNotificationFor('whatsapp') mengarah ke $phoneNumber.
        // WAJIB pakai trait Notifiable, bukan cuma routeNotificationFor() --
        // method notify() itu asalnya dari trait ini, bukan method biasa.
        $notifiable = $user ?? new class($phoneNumber)
        {
            use \Illuminate\Notifications\Notifiable;

            public function __construct(public string $phone_number) {}

            public function routeNotificationFor(string $channel): string
            {
                return $this->phone_number;
            }
        };

        $notifiable->notify(new SendOtpWhatsappNotification($plainOtp));

        return $otp;
    }

    private function validateContext(string $actionType, ?User $user): void
    {
        if ($actionType === 'activation' && $user !== null) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor ini sudah terdaftar sebagai akun aktif.',
            ]);
        }

        if (in_array($actionType, ['login', 'reset_password'], true) && $user === null) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor belum terdaftar, hubungi pihak sekolah.',
            ]);
        }
    }

    private function guardRateLimit(string $actionType, string $phoneNumber, ?User $user): void
    {
        $query = $user
            ? OtpCode::query()->where('user_id', $user->id)
            : OtpCode::query()->where('phone_number', $phoneNumber);

        $recentlyRequested = $query
            ->where('action_type', $actionType)
            ->where('created_at', '>=', now()->subSeconds(self::RATE_LIMIT_SECONDS))
            ->exists();

        if ($recentlyRequested) {
            throw ValidationException::withMessages([
                'phone_number' => 'Mohon tunggu sebentar sebelum meminta kode OTP baru.',
            ]);
        }
    }
}
