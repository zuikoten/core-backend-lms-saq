<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;

/**
 * Implementasi sementara: hanya menulis ke log, dipakai selama provider WA
 * (Fonnte/Watzhap) belum final. Binding-nya didaftarkan di
 * AuthModuleServiceProvider — ganti binding itu ke class provider asli
 * (mis. FonnteWhatsappGateway) tanpa perlu ubah kode Action manapun.
 */
class LogWhatsappGateway implements WhatsappGatewayInterface
{
    public function send(string $phoneNumber, string $message): bool
    {
        Log::info('[WhatsappGateway:stub] Pesan OTP', [
            'phone_number' => $phoneNumber,
            'message' => $message,
        ]);

        return true;
    }
}
