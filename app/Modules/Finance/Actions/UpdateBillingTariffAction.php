<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\BillingTariff;

class UpdateBillingTariffAction
{
    public function execute(BillingTariff $billingTariff, array $data): BillingTariff
    {
        $billingTariff->update($data);

        return $billingTariff;
    }
}