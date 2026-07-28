<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;

class WhatsappChannel
{
    public function __construct(private readonly WhatsappGatewayInterface $gateway)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $data = $notification->toWhatsapp($notifiable);

        $this->gateway->send($data['phone_number'], $data['message']);
    }
}
