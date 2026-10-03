<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;

class RecalculateInvoiceStatusAction
{
    /**
     * Status invoice dihitung ulang HANYA dari invoice_payments — satu-satunya
     * sumber kebenaran nominal yang sudah dibayar, baik manual maupun gateway
     * (gateway dicerminkan ke sini lewat HandleGatewayWebhookAction).
     * payment_gateway_transactions sengaja tidak ikut dihitung di sini: itu
     * cuma arsip/jejak proses pembayaran gateway, bukan catatan uang masuk —
     * ikut dihitung akan menyebabkan pembayaran yang sama kehitung dobel.
     */
    public function execute(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'cancelled') {
            return $invoice;
        }

        $totalPaid = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid');

        $status = match (true) {
            $totalPaid <= 0 => 'unpaid',
            $totalPaid >= $invoice->total_amount => 'paid',
            default => 'partial',
        };

        $invoice->update(['status' => $status]);

        return $invoice;
    }
}
