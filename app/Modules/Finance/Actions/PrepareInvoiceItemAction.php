<?php

namespace Modules\Finance\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\BillingTariff;

class PrepareInvoiceItemAction
{
    /**
     * Menyiapkan atribut 1 item invoice dari input form.
     *
     * - Item BEBAS (tanpa billing_tariff_id): dipakai apa adanya.
     * - Item dari TARIF: jenis tagihan DITURUNKAN dari tarif (bukan dipercaya
     *   dari input), tarif harus untuk tahun ajaran invoice, dan kalau nominal
     *   diubah dari nominal tarif, alasan (adjustment_note) wajib diisi —
     *   supaya penyimpangan dari tarif selalu punya jejak.
     *
     * @param  array  $item  billing_tariff_id?, billing_type_id, item_name, amount, adjustment_note?
     * @return array{billing_tariff_id: ?int, billing_type_id: int, item_name: string, amount: mixed, adjustment_note: ?string}
     */
    public function execute(array $item, ?int $academicYearId = null, string $errorKey = 'items'): array
    {
        $tariffId = $item['billing_tariff_id'] ?? null;

        if (! $tariffId) {
            return [
                'billing_tariff_id' => null,
                'billing_type_id' => (int) $item['billing_type_id'],
                'item_name' => $item['item_name'],
                'amount' => $item['amount'],
                'adjustment_note' => null,
            ];
        }

        $tariff = BillingTariff::query()->findOrFail($tariffId);

        if ($academicYearId !== null && (int) $tariff->academic_year_id !== $academicYearId) {
            throw ValidationException::withMessages([
                $errorKey => "Tarif \"{$tariff->tariff_name}\" bukan untuk tahun ajaran invoice ini.",
            ]);
        }

        $note = isset($item['adjustment_note']) ? trim((string) $item['adjustment_note']) : '';
        $note = $note === '' ? null : $note;

        $berbeda = round((float) $item['amount'], 2) !== round((float) $tariff->amount, 2);

        if ($berbeda && $note === null) {
            throw ValidationException::withMessages([
                $errorKey => "Item \"{$item['item_name']}\": nominal berbeda dari tarif (Rp".number_format((float) $tariff->amount, 0, ',', '.').'). Isi alasan penyesuaiannya.',
            ]);
        }

        return [
            'billing_tariff_id' => $tariff->id,
            'billing_type_id' => $tariff->billing_type_id,
            'item_name' => $item['item_name'],
            'amount' => $item['amount'],
            'adjustment_note' => $berbeda ? $note : null,
        ];
    }
}
