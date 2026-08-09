<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;

class PenyediaLayananWhatsappGateway implements WhatsappGatewayInterface
{
    public function send(string $phoneNumber, string $message): bool
    {
        $endpoint = config('services.whatsapp_gateway.url');
        $token = config('services.whatsapp_gateway.token');
        $targetField = config('services.whatsapp_gateway.target_field', 'target');
        $messageField = config('services.whatsapp_gateway.message_field', 'message');

        if (! $endpoint || ! $token) {
            Log::warning('[PenyediaLayananWhatsappGateway] URL/token belum dikonfigurasi, pesan tidak dikirim.', [
                'phone_number' => $phoneNumber,
            ]);

            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post($endpoint, [
            $targetField => $phoneNumber,
            $messageField => $message,
        ]);

        if ($response->failed()) {
            Log::error('[PenyediaLayananWhatsappGateway] Gagal mengirim pesan WhatsApp.', [
                'phone_number' => $phoneNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }
}