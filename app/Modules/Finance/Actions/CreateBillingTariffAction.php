<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\BillingTariff;

class CreateBillingTariffAction
{
    /**
     * Tarif dibuat per kombinasi jenis tagihan + tahun ajaran. tariff_name
     * jadi pembeda kalau 1 jenis tagihan punya nominal beda per kelompok
     * siswa (mis. "SPP TK-A" vs "SPP TK-B") — makanya bukan billing_type_id
     * saja yang unik per tahun ajaran, tapi kombinasi dengan tariff_name.
     */
    public function execute(array $data): BillingTariff
    {
        return BillingTariff::create($data);
    }
}