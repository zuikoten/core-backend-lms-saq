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

    /**
     * external_id disertakan timestamp (bukan invoice_number polos) supaya
     * orang tua bisa generate link baru kalau link sebelumnya sudah expired
     * — Xendit menolak external_id yang dipakai ulang.
     */
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

        $channel = PaymentChannel::query()
            ->where('channel_type', 'gateway')
            ->where('is_active', true)
            ->firstOrFail();

        $totalPaid = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid')
            + DB::table('payment_gateway_transactions')->where('invoice_id', $invoice->id)->where('status', 'paid')->sum('amount');
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
