<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Contracts\PaymentGatewayStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\PaymentGatewayTransaction;

class HandleGatewayWebhookAction
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private CreateInvoicePaymentAction $createInvoicePayment,
    ) {}

    /**
     * @return PaymentGatewayTransaction|null  null = transaksi tidak dikenal.
     *         Controller bisa memakai nilai kembalian ini untuk mengisi
     *         webhook_logs.payment_gateway_transaction_id dan processed.
     */
    public function execute(array $payload): ?PaymentGatewayTransaction
    {
        $result = $this->gateway->parseInvoiceWebhook($payload);

        return DB::transaction(function () use ($result) {
            // Lock baris transaksi: webhook ganda/retry menunggu di sini,
            // lalu membaca ulang status yang sudah 'paid' dan berhenti.
            $transaction = PaymentGatewayTransaction::where('external_id', $result->externalId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return null;
            }

            // Sudah diproses → idempoten.
            if ($transaction->status === 'paid') {
                return $transaction;
            }

            $isPaid = $result->status === PaymentGatewayStatus::Paid;

            // Status akhir non-paid (expired/cancelled/failed) boleh di-skip
            // untuk callback non-paid. Tapi callback PAID TIDAK BOLEH diabaikan
            // apa pun status lokalnya: uang sudah diterima Xendit (mis. link
            // lama yang sudah kita 'cancelled' tapi masih dibayar orang tua).
            if (! $isPaid && in_array($transaction->status, ['expired', 'cancelled', 'failed'], true)) {
                return $transaction;
            }

            $overpaidAmount = 0.0;

            if ($isPaid) {
                $invoice = Invoice::query()->whereKey($transaction->invoice_id)->lockForUpdate()->firstOrFail();

                $received = (float) $transaction->amount;
                $alreadyPaid = (float) DB::table('invoice_payments')
                    ->where('invoice_id', $invoice->id)
                    ->sum('amount_paid');
                $remaining = max(0.0, round((float) $invoice->total_amount - $alreadyPaid, 2));

                // Invoice dibatalkan → seluruh uang yang masuk dianggap lebih.
                $payable = $invoice->status === 'cancelled' ? 0.0 : min($remaining, $received);

                if ($payable > 0) {
                    // Sengaja TIDAK ada try/catch ValidationException: error
                    // yang tak terduga harus membatalkan transaction & membuat
                    // webhook gagal (Xendit retry), bukan diam-diam dianggap
                    // "kelebihan bayar".
                    $this->createInvoicePayment->execute($invoice, [
                        'payment_channel_id' => $transaction->payment_channel_id,
                        'reference_number' => $transaction->gateway_trx_id,
                        'amount_paid' => $payable,
                        'paid_at' => $result->paidAt ?? now(),
                        'handover_by' => null,
                        'payment_gateway_transaction_id' => $transaction->id,
                    ]);
                }

                $overpaidAmount = round($received - $payable, 2);
            }

            $transaction->update([
                'status' => $result->status->value,
                'paid_at' => $isPaid ? ($result->paidAt ?? now()) : null,
                'raw_response' => $result->rawPayload,
                'is_overpayment' => $overpaidAmount > 0,
                'overpaid_amount' => $overpaidAmount,
            ]);

            return $transaction;
        });
    }
}
