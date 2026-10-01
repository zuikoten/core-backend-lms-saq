<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;

class RecalculateInvoiceStatusAction
{
    /**
     * Status invoice dihitung ulang dari total pembayaran yang benar-benar
     * masuk (manual lewat invoice_payments + gateway yang statusnya 'paid'),
     * bukan disimpan manual — supaya tidak ada celah status tidak sinkron
     * dengan nominal yang sebenarnya sudah dibayar.
     */
    public function execute(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'cancelled') {
            return $invoice;
        }

        $totalPaid = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid')
            + DB::table('payment_gateway_transactions')->where('invoice_id', $invoice->id)->where('status', 'paid')->sum('amount');

        $status = match (true) {
            $totalPaid <= 0 => 'unpaid',
            $totalPaid >= $invoice->total_amount => 'paid',
            default => 'partial',
        };

        $invoice->update(['status' => $status]);

        return $invoice;
    }
}
