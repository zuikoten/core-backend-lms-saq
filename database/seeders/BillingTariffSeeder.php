<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\AcademicYear;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\BillingType;

class BillingTariffSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            $this->command->warn('Tidak ada academic_year aktif, BillingTariffSeeder dilewati.');
            return;
        }

        $tariffs = [
            [
                'billing_type_name' => 'SPP',
                'tariff_name' => 'SPP Reguler',
                'amount' => 350000,
            ],
            [
                'billing_type_name' => 'Tabungan Wajib',
                'tariff_name' => 'Tabungan Wajib Minimum',
                'amount' => 50000,
            ],
        ];

        foreach ($tariffs as $tariff) {
            $billingType = BillingType::where('name', $tariff['billing_type_name'])->first();

            if (! $billingType) {
                $this->command->warn("BillingType '{$tariff['billing_type_name']}' tidak ditemukan, dilewati.");
                continue;
            }

            BillingTariff::firstOrCreate(
                [
                    'billing_type_id' => $billingType->id,
                    'academic_year_id' => $activeYear->id,
                ],
                [
                    'tariff_name' => $tariff['tariff_name'],
                    'amount' => $tariff['amount'],
                ]
            );
        }
    }
}