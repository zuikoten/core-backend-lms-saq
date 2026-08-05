<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\BillingType;

class DeleteBillingTypeAction
{
    /**
     * Jenis tagihan yang sudah dipakai di tarif (billing_tariffs) atau sudah
     * pernah dijadikan item invoice (invoice_items) tidak boleh dihapus —
     * invoice lama menyimpan snapshot nama item, tapi kalau jenis tagihan
     * induknya hilang, penelusuran & laporan per jenis tagihan jadi rusak.
     * Query lewat DB::table() (bukan Model billing_tariffs/invoice_items)
     * karena Model itu belum ada — nanti tetap query by nama tabel meski
     * sudah ada, mengikuti prinsip guard lintas entitas dalam 1 modul.
     */
    public function execute(BillingType $billingType): void
    {
        $dipakaiDiTarif = DB::table('billing_tariffs')
            ->where('billing_type_id', $billingType->id)
            ->exists();

        $dipakaiDiInvoice = DB::table('invoice_items')
            ->where('billing_type_id', $billingType->id)
            ->exists();

        if ($dipakaiDiTarif || $dipakaiDiInvoice) {
            throw ValidationException::withMessages([
                'name' => 'Jenis tagihan ini masih dipakai di tarif atau invoice, tidak bisa dihapus.',
            ]);
        }

        $billingType->delete();
    }
}
