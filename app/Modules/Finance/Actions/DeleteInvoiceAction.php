<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Invoice;

class DeleteInvoiceAction
{
    /**
     * Invoice yang sudah ada pembayaran (invoice_payments atau
     * payment_gateway_transactions) tidak boleh dihapus — riwayat
     * keuangan harus tetap bisa ditelusuri.
     */
    public function execute(Invoice $invoice): void
    {
        $sudahDibayar = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->exists()
            || DB::table('payment_gateway_transactions')->where('invoice_id', $invoice->id)->exists();

        if ($sudahDibayar) {
            throw ValidationException::withMessages([
                'invoice_number' => 'Invoice ini sudah ada pembayaran, tidak bisa dihapus.',
            ]);
        }

        $invoice->delete();
    }
}