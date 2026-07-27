<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_channel_id',
        'payment_gateway_transaction_id',
        'reference_number',
        'amount_paid',
        'paid_at',
        'handover_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function gatewayTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayTransaction::class);
    }

    public function handoverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handover_by');
    }
}