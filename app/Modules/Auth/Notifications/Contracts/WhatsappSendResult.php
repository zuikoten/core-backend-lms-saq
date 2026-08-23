<?php

namespace Modules\Auth\Notifications\Contracts;

final class WhatsappSendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
    ) {}
}
