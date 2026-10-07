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

    /**
     * Seluruh proses dibungkus transaction + lock pada baris invoice, supaya
     * pencatatan manual oleh staf yang kebetulan bersamaan dengan webhook
     * gateway tidak sama-sama lolos cek sisa tagihan. Lock aman dipanggil
     * ulang kalau pemanggilnya (webhook) sudah mengunci baris yang sama di
     * transaction yang sama.
     */
    public function execute(Invoice $invoice, array $data): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

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
            $totalPaid = (float) DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('amount_paid');

            // Dibulatkan 2 desimal di kedua sisi supaya selisih floating point
            // tidak memicu penolakan palsu "melebihi sisa tagihan".
            $remaining = round((float) $invoice->total_amount - $totalPaid, 2);

            if (round((float) $data['amount_paid'], 2) > $remaining) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Nominal melebihi sisa tagihan (Rp'.number_format($remaining, 0, ',', '.').'). Sesuaikan nominalnya.',
                ]);
            }

            $payment = $invoice->payments()->create($data);

            $this->recalculateInvoiceStatus->execute($invoice);

            return $payment;
        });
    }
}
