<?php

namespace Modules\Finance\Contracts;

interface PaymentGatewayInterface
{
    public function createInvoice(
        string $externalId,
        float $amount,
        ?string $payerEmail,  // <- nullable
        string $description,
        int $durationSeconds,
    ): PaymentGatewayInvoiceResult;

    public function parseInvoiceWebhook(array $payload): PaymentGatewayWebhookResult;
}
