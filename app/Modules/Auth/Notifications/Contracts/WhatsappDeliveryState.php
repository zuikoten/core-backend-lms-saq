<?php

namespace Modules\Auth\Notifications\Contracts;

/**
 * Status SERAGAM lintas provider -- tiap gateway (FonnteWhatsappGateway,
 * dst) yang nerjemahin status mentah punya provider masing-masing ke
 * enum ini lewat parseStatusWebhook(). Kode pemanggil (webhook controller,
 * endpoint polling) cuma kerja ke enum ini, gak pernah baca payload mentah
 * provider tertentu.
 */
enum WhatsappDeliveryState: string
{
    case Sent = 'sent';
    case Invalid = 'invalid';     // nomor gak terdaftar WhatsApp
    case Pending = 'pending';
    case Expired = 'expired';
    case Unknown = 'unknown';     // status dari provider yang belum kita kenali
}
