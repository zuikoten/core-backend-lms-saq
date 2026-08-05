<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\BillingType;

class UpdateBillingTypeAction
{
    public function execute(BillingType $billingType, array $data): BillingType
    {
        $billingType->update($data);

        return $billingType;
    }
}
