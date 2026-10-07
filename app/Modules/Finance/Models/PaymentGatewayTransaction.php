<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGatewayTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_channel_id',
        'external_id',
        'gateway_trx_id',
        'invoice_url',
        'status',
        'is_overpayment',
        'overpaid_amount',
        'amount',
        'expired_at',
        'paid_at',
        'raw_request',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'overpaid_amount' => 'decimal:2',
            'is_overpayment' => 'boolean',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'raw_request' => 'array',
            'raw_response' => 'array',
        ];
    }

    /**
     * Transaksi yang menerima uang lebih dari yang dibutuhkan invoice
     * (perlu direfund / dialihkan oleh bendahara).
     */
    public function scopeOverpaid(Builder $query): Builder
    {
        return $query->where('overpaid_amount', '>', 0);
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
