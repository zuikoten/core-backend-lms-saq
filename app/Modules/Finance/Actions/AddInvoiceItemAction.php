<?php

namespace Modules\Finance\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Invoice;

class AddInvoiceItemAction
{
    /**
     * Hanya invoice berstatus 'unpaid' yang boleh ditambah item — begitu
     * ada pembayaran (partial/paid), nominal invoice tidak boleh berubah
     * lagi supaya rekonsiliasi pembayaran tetap akurat.
     */
    public function execute(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status !== 'unpaid') {
            throw ValidationException::withMessages([
                'amount' => 'Invoice ini sudah ada pembayaran, tidak bisa ditambah item baru.',
            ]);
        }

        $invoice->items()->create($data);
        $invoice->update(['total_amount' => $invoice->items()->sum('amount')]);

        return $invoice->fresh('items');
    }
}