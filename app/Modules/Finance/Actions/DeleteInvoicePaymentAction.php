<?php

namespace Modules\Finance\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\InvoicePayment;

class DeleteInvoicePaymentAction
{
    public function __construct(
        private RecalculateInvoiceStatusAction $recalculateInvoiceStatus,
    ) {}

    /**
     * Pembayaran yang berasal dari gateway (payment_gateway_transaction_id
     * terisi) tidak boleh dihapus lewat sini — itu representasi transaksi
     * yang benar-benar terjadi di sisi Finpay, koreksinya harus lewat alur
     * gateway, bukan dihapus manual dari sisi kita.
     */
    public function execute(InvoicePayment $invoicePayment): void
    {
        if ($invoicePayment->payment_gateway_transaction_id !== null) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Pembayaran ini berasal dari payment gateway, tidak bisa dihapus manual.',
            ]);
        }

        $invoice = $invoicePayment->invoice;
        $invoicePayment->delete();

        $this->recalculateInvoiceStatus->execute($invoice);
    }
}
