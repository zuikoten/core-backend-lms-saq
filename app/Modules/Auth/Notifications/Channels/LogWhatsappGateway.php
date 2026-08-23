<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Contracts\WhatsappDeliveryState;
use Modules\Auth\Notifications\Contracts\WhatsappDeliveryStatus;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;
use Modules\Auth\Notifications\Contracts\WhatsappSendResult;

class LogWhatsappGateway implements WhatsappGatewayInterface
{
    public function send(string $phoneNumber, string $message): WhatsappSendResult
    {
        Log::info('[WhatsappGateway:stub] Pesan OTP', [
            'phone_number' => $phoneNumber,
            'message' => $message,
        ]);

        return new WhatsappSendResult(success: true, messageId: 'stub-'.uniqid());
    }

    public function parseStatusWebhook(array $payload): WhatsappDeliveryStatus
    {
        return new WhatsappDeliveryStatus(
            messageId: (string) ($payload['id'] ?? ''),
            state: WhatsappDeliveryState::Sent,
        );
    }
}
