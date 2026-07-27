<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    public $timestamps = false; // hanya created_at, tidak ada updated_at

    protected $fillable = [
        'provider',
        'payload',
        'headers',
        'signature_valid',
        'processed',
        'payment_gateway_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'signature_valid' => 'boolean',
            'processed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function gatewayTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayTransaction::class, 'payment_gateway_transaction_id');
    }
}