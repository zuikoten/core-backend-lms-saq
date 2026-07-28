<?php

namespace Modules\Auth\Notifications\Contracts;

interface WhatsappGatewayInterface
{
    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string  $phoneNumber  Format E.164 tanpa tanda "+", mis. 6281234567890
     * @param  string  $message
     * @return bool  true jika gateway menerima pesan untuk dikirim
     *
     * @throws \Modules\Auth\Exceptions\WhatsappGatewayException
     */
    public function send(string $phoneNumber, string $message): bool;
}
