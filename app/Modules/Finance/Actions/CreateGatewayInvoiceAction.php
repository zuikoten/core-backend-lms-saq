<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\PaymentChannel;
use Modules\Finance\Models\PaymentGatewayTransaction;

class CreateGatewayInvoiceAction
{
    public function __construct(private PaymentGatewayInterface $gateway) {}

    public function execute(Invoice $invoice): PaymentGatewayTransaction
    {
        if ($invoice->status === 'cancelled') {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice ini sudah dibatalkan, tidak bisa dibuatkan tagihan online.',
            ]);
        }

        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'invoice' => 'Tagihan ini sudah lunas.',
            ]);
        }

        // Kalau masih ada transaksi gateway yang 'pending' dan belum expired
        // untuk invoice yang sama, pakai ulang link itu — JANGAN bikin baru.
        // Ini yang mencegah 2 metode bayar aktif bersamaan untuk 1 invoice,
        // yang kalau dibiarkan bisa berujung dibayar 2x (kasus Mizan 2).
        $existing = PaymentGatewayTransaction::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $channel = PaymentChannel::query()
            ->where('channel_type', 'gateway')
            ->where('is_active', true)
            ->firstOrFail();

        $totalPaid = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid');
        $sisaTagihan = $invoice->total_amount - $totalPaid;

        $externalId = 'INV-'.$invoice->id.'-'.now()->timestamp;

        $result = $this->gateway->createInvoice(
            externalId: $externalId,
            amount: $sisaTagihan,
            payerEmail: $invoice->student->parentProfile->user->email,
            description: "Pembayaran {$invoice->invoice_number}",
            durationSeconds: 86400,
        );

        return PaymentGatewayTransaction::create([
            'invoice_id' => $invoice->id,
            'payment_channel_id' => $channel->id,
            'external_id' => $externalId,
            'gateway_trx_id' => $result->gatewayTrxId,
            'invoice_url' => $result->invoiceUrl,
            'status' => 'pending',
            'amount' => $sisaTagihan,
            'expired_at' => $result->expiredAt,
            'raw_response' => $result->rawResponse,
        ]);
    }
}
