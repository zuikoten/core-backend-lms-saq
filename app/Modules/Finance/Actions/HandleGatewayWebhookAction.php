<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Contracts\PaymentGatewayStatus;
use Modules\Finance\Models\PaymentGatewayTransaction;

class HandleGatewayWebhookAction
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private CreateInvoicePaymentAction $createInvoicePayment,
    ) {}

    public function execute(array $payload): void
    {
        $result = $this->gateway->parseInvoiceWebhook($payload);

        $transaction = PaymentGatewayTransaction::where('external_id', $result->externalId)->first();

        if (! $transaction || in_array($transaction->status, ['paid', 'expired', 'cancelled'])) {
            return;
        }

        DB::transaction(function () use ($transaction, $result) {
            // Catat ke invoice_payments DULU, sebelum transaksi gateway
            // ditandai 'paid' — supaya CreateInvoicePaymentAction menghitung
            // sisa tagihan dari invoice_payments yang masih "bersih" (belum
            // ketambah transaksi ini dari sisi manapun).
            if ($result->status === PaymentGatewayStatus::Paid) {
                $this->createInvoicePayment->execute($transaction->invoice, [
                    'payment_channel_id' => $transaction->payment_channel_id,
                    'reference_number' => $transaction->gateway_trx_id,
                    'amount_paid' => $transaction->amount,
                    // Waktu ASLI dari Xendit, bukan now() — now() cuma menunjukkan
                    // kapan WEBHOOK diproses, bisa telat dari kejadian bayar
                    // sebenarnya kalau ada retry/downtime/resend manual.
                    'paid_at' => $result->paidAt ?? now(),
                    'handover_by' => null,
                    'payment_gateway_transaction_id' => $transaction->id,
                ]);
            }

            $transaction->update([
                'status' => $result->status->value,
                'paid_at' => $result->status === PaymentGatewayStatus::Paid ? ($result->paidAt ?? now()) : null,
                'raw_response' => $result->rawPayload,
            ]);
        });
    }
}
