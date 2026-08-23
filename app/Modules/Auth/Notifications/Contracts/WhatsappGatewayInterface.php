<?php

namespace Modules\Auth\Notifications\Contracts;

interface WhatsappGatewayInterface
{
    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string  $phoneNumber  Format E.164 tanpa tanda "+", mis. 6281234567890
     * @return WhatsappSendResult
     *
     * @throws \Modules\Auth\Exceptions\WhatsappGatewayException
     */
    public function send(string $phoneNumber, string $message): WhatsappSendResult;

    /**
     * Terjemahkan payload MENTAH webhook status (bentuknya beda-beda tiap
     * provider) jadi struktur SERAGAM yang dipahami kode kita. Ini titik
     * SATU-SATUNYA yang perlu tau bentuk asli payload provider tertentu.
     */
    public function parseStatusWebhook(array $payload): WhatsappDeliveryStatus;
}
