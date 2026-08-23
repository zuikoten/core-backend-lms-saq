<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Contracts\WhatsappDeliveryState;
use Modules\Auth\Notifications\Contracts\WhatsappDeliveryStatus;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;
use Modules\Auth\Notifications\Contracts\WhatsappSendResult;

/**
 * Pengganti PenyediaLayananWhatsappGateway -- nama disesuaikan eksplisit
 * ke provider yang sedang dites (Fonnte), karena parseStatusWebhook() di
 * bawah ini SPESIFIK ke bentuk payload Fonnte (docs.fonnte.com), bukan lagi
 * generic. Kalau provider final beda, bikin class baru serupa ini +
 * ganti 1 baris binding di AuthModuleServiceProvider.
 */
class FonnteWhatsappGateway implements WhatsappGatewayInterface
{
    public function send(string $phoneNumber, string $message): WhatsappSendResult
    {
        $endpoint = config('services.whatsapp_gateway.url');
        $token = config('services.whatsapp_gateway.token');

        if (! $endpoint || ! $token) {
            Log::warning('[FonnteWhatsappGateway] URL/token belum dikonfigurasi, pesan tidak dikirim.', [
                'phone_number' => $phoneNumber,
            ]);

            return new WhatsappSendResult(success: false);
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post($endpoint, [
            'target' => $phoneNumber,
            'message' => $message,
        ]);

        if ($response->failed()) {
            Log::error('[FonnteWhatsappGateway] Gagal mengirim pesan WhatsApp.', [
                'phone_number' => $phoneNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new WhatsappSendResult(success: false);
        }

        // Bentuk respons Fonnte: {"id": ["123"], "target": ["628..."], ...}
        // -- "id" adalah ARRAY (API-nya support multi-target sekaligus),
        // kita cuma kirim 1 target jadi ambil elemen pertama.
        $body = $response->json();
        $messageId = $body['id'][0] ?? null;

        return new WhatsappSendResult(success: true, messageId: $messageId);
    }

    /**
     * Bentuk payload webhook Fonnte: {device, id, stateid, status, state}
     * (docs.fonnte.com/webhook-update-message-status). Daftar value
     * "status" yang diketahui: Sent/Invalid/Pending/Waiting/Processing/
     * Expired/Url unreachable (docs.fonnte.com/api-check-message-status).
     */
    public function parseStatusWebhook(array $payload): WhatsappDeliveryStatus
    {
        $state = match (strtolower((string) ($payload['status'] ?? ''))) {
            'sent' => WhatsappDeliveryState::Sent,
            'invalid' => WhatsappDeliveryState::Invalid,
            'pending', 'processing', 'waiting' => WhatsappDeliveryState::Pending,
            'expired' => WhatsappDeliveryState::Expired,
            default => WhatsappDeliveryState::Unknown,
        };

        return new WhatsappDeliveryStatus(
            messageId: (string) ($payload['id'] ?? ''),
            state: $state,
        );
    }
}
