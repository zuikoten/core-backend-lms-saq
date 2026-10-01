<?php

namespace Modules\Finance\Contracts;

readonly class PaymentGatewayInvoiceResult
{
    public function __construct(
        public string $gatewayTrxId,
        public string $invoiceUrl,
        public \DateTimeInterface $expiredAt,
        public array $rawResponse,
    ) {}
}
