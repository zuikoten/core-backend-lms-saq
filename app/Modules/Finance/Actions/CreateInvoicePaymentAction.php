<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoicePayment;

class CreateInvoicePaymentAction
{
    public function __construct(
        private RecalculateInvoiceStatusAction $recalculateInvoiceStatus,
    ) {}

    public function execute(Invoice $invoice, array $data): InvoicePayment
    {
        if ($invoice->status === 'cancelled') {
            throw ValidationException::withMessages([
                'amount_paid' => 'Invoice ini sudah dibatalkan, tidak bisa menerima pembayaran.',
            ]);
        }

        // invoice_payments adalah SATU-SATUNYA sumber kebenaran nominal yang
        // sudah dibayar — termasuk pembayaran dari gateway, karena setiap
        // transaksi gateway yang sukses selalu dicerminkan jadi 1 baris di
        // sini juga (lihat HandleGatewayWebhookAction). payment_gateway_transactions
        // sengaja TIDAK ikut dijumlah di sini, biar tidak dihitung dobel.
        $totalPaid = DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid');

        $remaining = $invoice->total_amount - $totalPaid;

        if ($data['amount_paid'] > $remaining) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Nominal melebihi sisa tagihan (Rp'.number_format($remaining, 0, ',', '.').'). Sesuaikan nominalnya.',
            ]);
        }

        $payment = $invoice->payments()->create($data);

        $this->recalculateInvoiceStatus->execute($invoice);

        return $payment;
    }
}
