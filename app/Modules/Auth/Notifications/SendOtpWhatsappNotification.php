<?php

namespace Modules\Auth\Notifications;

use Illuminate\Notifications\Notification;

class SendOtpWhatsappNotification extends Notification
{
    public function __construct(private readonly string $otpCode)
    {
    }

    public function via(object $notifiable): array
    {
        return ['whatsapp'];
    }

    /**
     * Dipanggil oleh custom notification channel (lihat AuthModuleServiceProvider,
     * where we extend Illuminate\Notifications\ChannelManager) ATAU dipanggil
     * langsung dari Action lewat WhatsappGatewayInterface — di sini kita pakai
     * pendekatan langsung supaya lebih eksplisit & mudah ditelusuri.
     */
    public function toWhatsapp(object $notifiable): array
    {
        return [
            'phone_number' => $notifiable->phone_number ?? $notifiable->routeNotificationFor('whatsapp'),
            'message' => $this->message(),
        ];
    }

    public function message(): string
    {
        return "Kode OTP Anda: {$this->otpCode}. Berlaku 5 menit. Jangan berikan kode ini kepada siapapun.";
    }
}
