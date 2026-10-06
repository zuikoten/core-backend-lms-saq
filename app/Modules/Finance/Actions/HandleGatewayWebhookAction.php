<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

        DB::transaction(function () use ($result) {
            // lockForUpdate() di DALAM transaction — baris ini terkunci sampai
            // transaction selesai commit/rollback. Kalau webhook yang sama
            // masuk 2x nyaris bersamaan (bukan cuma dari resend manual, tapi
            // juga retry asli dari Xendit), request kedua akan NUNGGU di baris
            // ini sampai request pertama kelar, baru baca ulang status —
            // yang saat itu sudah 'paid', jadi otomatis ke-skip oleh guard.
            $transaction = PaymentGatewayTransaction::where('external_id', $result->externalId)
                ->lockForUpdate()
                ->first();

            if (! $transaction || in_array($transaction->status, ['paid', 'expired', 'cancelled'])) {
                return;
            }

            $isOverpayment = false;

            if ($result->status === PaymentGatewayStatus::Paid) {
                try {
                    $this->createInvoicePayment->execute($transaction->invoice, [
                        'payment_channel_id' => $transaction->payment_channel_id,
                        'reference_number' => $transaction->gateway_trx_id,
                        'amount_paid' => $transaction->amount,
                        'paid_at' => $result->paidAt ?? now(),
                        'handover_by' => null,
                        'payment_gateway_transaction_id' => $transaction->id,
                    ]);
                } catch (ValidationException $e) {
                    $isOverpayment = true;
                }
            }

            $transaction->update([
                'status' => $result->status->value,
                'paid_at' => $result->status === PaymentGatewayStatus::Paid ? ($result->paidAt ?? now()) : null,
                'raw_response' => $result->rawPayload,
                'is_overpayment' => $isOverpayment,
            ]);
        });
    }
}
