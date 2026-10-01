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

    /**
     * Idempotent sengaja: gateway retry webhook kalau respons kita
     * telat/gagal, jadi transaksi yang statusnya sudah final (paid/expired/
     * cancelled) tidak diproses dobel — mencegah InvoicePayment ganda.
     */
    public function execute(array $payload): void
    {
        $result = $this->gateway->parseInvoiceWebhook($payload);

        $transaction = PaymentGatewayTransaction::where('external_id', $result->externalId)->first();

        if (! $transaction || in_array($transaction->status, ['paid', 'expired', 'cancelled'])) {
            return;
        }

        DB::transaction(function () use ($transaction, $result) {
            $transaction->update([
                'status' => $result->status->value,
                'paid_at' => $result->status === PaymentGatewayStatus::Paid ? now() : null,
                'raw_response' => $result->rawPayload,
            ]);

            if ($result->status === PaymentGatewayStatus::Paid) {
                // handover_by null: tidak ada staf yang input, ini otomatis
                // dari gateway — pembeda "berasal dari gateway" tetap lewat
                // payment_gateway_transaction_id, bukan handover_by.
                $this->createInvoicePayment->execute($transaction->invoice, [
                    'payment_channel_id' => $transaction->payment_channel_id,
                    'reference_number' => $transaction->gateway_trx_id,
                    'amount_paid' => $transaction->amount,
                    'paid_at' => now(),
                    'handover_by' => null,
                    'payment_gateway_transaction_id' => $transaction->id,
                ]);
            }
        });
    }
}
