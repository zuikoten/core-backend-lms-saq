<?php

namespace Modules\Finance\Contracts;

readonly class PaymentGatewayWebhookResult
{
    public function __construct(
        public string $externalId,
        public PaymentGatewayStatus $status,
        public array $rawPayload,
    ) {}
}
