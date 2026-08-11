<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetPaymentChannelRecapAction
{
    /**
     * Direkap dari invoice_payments berdasarkan paid_at (kapan uang
     * BENAR-BENAR diterima), bukan created_at (kapan staf sempat
     * menginputnya). payment_gateway_transactions belum ikut karena
     * Finpay belum diintegrasikan.
     */
    public function execute(?string $dateFrom, ?string $dateTo): Collection
    {
        return DB::table('invoice_payments')
            ->join('payment_channels', 'payment_channels.id', '=', 'invoice_payments.payment_channel_id')
            ->when($dateFrom, fn ($query) => $query->whereDate('invoice_payments.paid_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('invoice_payments.paid_at', '<=', $dateTo))
            ->selectRaw('payment_channels.id, payment_channels.name, payment_channels.channel_type, SUM(invoice_payments.amount_paid) as total_diterima, COUNT(*) as jumlah_transaksi')
            ->groupBy('payment_channels.id', 'payment_channels.name', 'payment_channels.channel_type')
            ->orderByDesc('total_diterima')
            ->get();
    }
}