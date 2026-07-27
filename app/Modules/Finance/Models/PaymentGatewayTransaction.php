<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGatewayTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_channel_id',
        'gateway_reference_id',
        'gateway_trx_id',
        'channel_data',
        'status',
        'amount',
        'expired_at',
        'paid_at',
        'raw_request',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'channel_data' => 'array',
            'amount' => 'decimal:2',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'raw_request' => 'array',
            'raw_response' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentChannel(): BelongsTo
    {
        return $this->belongsTo(PaymentChannel::class);
    }

    public function invoicePayment(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }
}
