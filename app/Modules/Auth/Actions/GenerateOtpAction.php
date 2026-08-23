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
     * @param  string  $actionType  'activation' | 'login' | 'reset_password' | 'change_phone'
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

        // Notifiable SELALU dituju ke $phoneNumber param, BUKAN $user langsung --
        // $user->phone_number yang tersimpan di database bisa beda dari nomor
        // tujuan OTP kali ini (kasus change_phone: OTP wajib ke nomor BARU, bukan
        // nomor lama yang masih tersimpan di $user sampai proses ini selesai).
        // WAJIB pakai trait Notifiable, bukan cuma routeNotificationFor() --
        // method notify() itu asalnya dari trait ini, bukan method biasa.
        $notifiable = new class($phoneNumber)
        {
            use \Illuminate\Notifications\Notifiable;

            public function __construct(public string $phone_number) {}

            public function routeNotificationFor(string $channel): string
            {
                return $this->phone_number;
            }
        };

        $notifiable->notify(new SendOtpWhatsappNotification(
            otpCode: $plainOtp,
            onSent: function (\Modules\Auth\Notifications\Contracts\WhatsappSendResult $result) use ($otp) {
                if ($result->messageId) {
                    $otp->update(['gateway_message_id' => $result->messageId]);
                }
            },
        ));

        return $otp;
    }

    private function validateContext(string $actionType, ?User $user): void
    {
        if ($actionType === 'activation' && $user !== null) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor ini sudah terdaftar sebagai akun aktif.',
            ]);
        }

        if (in_array($actionType, ['login', 'reset_password', 'change_phone'], true) && $user === null) {
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
