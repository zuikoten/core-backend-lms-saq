<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\BillingTariff;

class DeleteBillingTariffAction
{
    /**
     * Tarif yang sudah dipetakan ke siswa lewat student_tariff_mappings
     * tidak boleh dihapus — kalau tarifnya hilang, pemetaan siswa jadi
     * yatim dan proses generate invoice bulanan nanti kehilangan acuan
     * nominal.
     */
    public function execute(BillingTariff $billingTariff): void
    {
        $dipakaiDiPemetaan = DB::table('student_tariff_mappings')
            ->where('billing_tariff_id', $billingTariff->id)
            ->exists();

        if ($dipakaiDiPemetaan) {
            throw ValidationException::withMessages([
                'tariff_name' => 'Tarif ini masih dipetakan ke siswa, tidak bisa dihapus.',
            ]);
        }

        $billingTariff->delete();
    }
}