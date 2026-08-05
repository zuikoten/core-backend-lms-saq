<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\PaymentChannel;

class UpdatePaymentChannelAction
{
    public function execute(PaymentChannel $paymentChannel, array $data): PaymentChannel
    {
        $paymentChannel->update($data);

        return $paymentChannel;
    }
}
