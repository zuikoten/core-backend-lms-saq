<?php

namespace Modules\Finance\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\InvoiceItem;

class DeleteInvoiceItemAction
{
    public function execute(InvoiceItem $invoiceItem): void
    {
        $invoice = $invoiceItem->invoice;

        if ($invoice->status !== 'unpaid') {
            throw ValidationException::withMessages([
                'amount' => 'Invoice ini sudah ada pembayaran, item tidak bisa dihapus.',
            ]);
        }

        $invoiceItem->delete();
        $invoice->update(['total_amount' => $invoice->items()->sum('amount')]);
    }
}