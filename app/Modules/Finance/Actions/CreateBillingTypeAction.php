<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\BillingType;

class CreateBillingTypeAction
{
    /**
     * Jenis tagihan adalah master data referensi untuk billing_tariffs dan
     * invoice_items — dibuat sekali, dipakai berulang lintas tahun ajaran.
     */
    public function execute(array $data): BillingType
    {
        return BillingType::create($data);
    }
}
