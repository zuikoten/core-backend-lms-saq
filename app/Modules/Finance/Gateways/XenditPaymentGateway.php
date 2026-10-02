<?php

namespace Modules\Finance\Gateways;

use Illuminate\Support\Facades\Http;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Contracts\PaymentGatewayInvoiceResult;
use Modules\Finance\Contracts\PaymentGatewayStatus;
use Modules\Finance\Contracts\PaymentGatewayWebhookResult;

class XenditPaymentGateway implements PaymentGatewayInterface
{
    public function createInvoice(
        string $externalId,
        float $amount,
        ?string $payerEmail,
        string $description,
        int $durationSeconds,
    ): PaymentGatewayInvoiceResult {
        $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
            ->post('https://api.xendit.co/v2/invoices', array_filter([
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $payerEmail,
                'description' => $description,
                'invoice_duration' => $durationSeconds,
                'currency' => 'IDR',
            ], fn($value) => $value !== null))
            ->throw()
            ->json();

        return new PaymentGatewayInvoiceResult(
            gatewayTrxId: $response['id'],
            invoiceUrl: $response['invoice_url'],
            expiredAt: new \DateTimeImmutable($response['expiry_date']),
            rawResponse: $response,
        );
    }

    /**
     * SETTLED diperlakukan sama seperti PAID (keduanya berarti dana sudah
     * bisa dianggap masuk dari sisi bisnis kita, walau Xendit bedakan
     * "settlement ke rekening" vs "pembayaran diterima").
     */
    public function parseInvoiceWebhook(array $payload): PaymentGatewayWebhookResult
    {
        $status = match ($payload['status']) {
            'PAID', 'SETTLED' => PaymentGatewayStatus::Paid,
            'EXPIRED' => PaymentGatewayStatus::Expired,
            default => PaymentGatewayStatus::Failed,
        };

        return new PaymentGatewayWebhookResult(
            externalId: $payload['external_id'],
            status: $status,
            rawPayload: $payload,
        );
    }
}
