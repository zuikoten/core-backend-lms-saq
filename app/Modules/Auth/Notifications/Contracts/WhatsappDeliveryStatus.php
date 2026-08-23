<?php

namespace Modules\Auth\Notifications\Contracts;

final class WhatsappDeliveryStatus
{
    public function __construct(
        public readonly string $messageId,
        public readonly WhatsappDeliveryState $state,
    ) {}
}
