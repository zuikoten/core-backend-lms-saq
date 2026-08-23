<?php

namespace Modules\Auth\Notifications;

use Illuminate\Notifications\Notification;

class SendOtpWhatsappNotification extends Notification
{
    public function __construct(
        private readonly string $otpCode,
        private readonly ?\Closure $onSent = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsapp(object $notifiable): array
    {
        return [
            'phone_number' => $notifiable->phone_number ?? $notifiable->routeNotificationFor('whatsapp'),
            'message' => $this->message(),
            'on_sent' => $this->onSent,
        ];
    }

    public function message(): string
    {
        return "Kode OTP Anda: {$this->otpCode}. Berlaku 5 menit. Jangan berikan kode ini kepada siapapun.";
    }
}
