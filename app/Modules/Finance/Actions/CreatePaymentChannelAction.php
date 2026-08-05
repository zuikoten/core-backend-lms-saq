<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\PaymentChannel;

class CreatePaymentChannelAction
{
    /**
     * Kanal pembayaran dipakai baik untuk pencatatan manual (transfer/tunai)
     * maupun sebagai referensi kanal saat integrasi Finpay sudah final —
     * provider 'manual' vs 'finpay' membedakan dua alur itu di kode nanti.
     */
    public function execute(array $data): PaymentChannel
    {
        return PaymentChannel::create($data);
    }
}
