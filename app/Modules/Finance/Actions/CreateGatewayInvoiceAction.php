<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Contracts\PaymentGatewayInterface;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\PaymentChannel;
use Modules\Finance\Models\PaymentGatewayTransaction;

class CreateGatewayInvoiceAction
{
    public function __construct(private PaymentGatewayInterface $gateway) {}

    /**
     * Seluruh proses (cek status → cek link lama → buat link baru) dibungkus
     * satu DB transaction dengan lock pada baris invoice. Dua klik "Bayar
     * online" yang nyaris bersamaan jadi antre: request kedua baru jalan
     * setelah yang pertama selesai, lalu menemukan link yang baru dibuat
     * dan memakainya ulang — bukan membuat link kedua.
     */
    public function execute(Invoice $invoice): PaymentGatewayTransaction
    {
        return DB::transaction(function () use ($invoice) {
            // Baca ulang SETELAH lock, supaya status & total yang dicek
            // adalah kondisi terbaru (mis. webhook baru saja menandai lunas).
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'invoice' => 'Invoice ini sudah dibatalkan, tidak bisa dibuatkan tagihan online.',
                ]);
            }

            if ($invoice->status === 'paid') {
                throw ValidationException::withMessages([
                    'invoice' => 'Tagihan ini sudah lunas.',
                ]);
            }

            $totalPaid = (float) DB::table('invoice_payments')
                ->where('invoice_id', $invoice->id)
                ->sum('amount_paid');
            $sisaTagihan = round((float) $invoice->total_amount - $totalPaid, 2);

            if ($sisaTagihan <= 0) {
                throw ValidationException::withMessages([
                    'invoice' => 'Tagihan ini sudah lunas.',
                ]);
            }

            $existing = PaymentGatewayTransaction::query()
                ->where('invoice_id', $invoice->id)
                ->where('status', 'pending')
                ->where('expired_at', '>', now())
                ->latest('id')
                ->first();

            // Link lama hanya boleh dipakai ulang kalau nominalnya MASIH sama
            // dengan sisa tagihan sekarang. Kalau item invoice berubah atau
            // ada pembayaran parsial manual, link lama sudah usang.
            if ($existing && abs((float) $existing->amount - $sisaTagihan) < 0.005) {
                return $existing;
            }

            $email = $invoice->student?->parentProfile?->user?->email;

            if (! $email) {
                throw ValidationException::withMessages([
                    'invoice' => 'Siswa ini belum terhubung ke akun orang tua yang punya email, tagihan online tidak bisa dibuat.',
                ]);
            }

            $channel = PaymentChannel::query()
                ->where('channel_type', 'gateway')
                ->where('is_active', true)
                ->firstOrFail();

            $externalId = 'INV-' . $invoice->id . '-' . now()->timestamp;

            $result = $this->gateway->createInvoice(
                externalId: $externalId,
                amount: $sisaTagihan,
                payerEmail: $email,
                description: "Pembayaran {$invoice->invoice_number}",
                durationSeconds: 86400,
            );

            $transaction = PaymentGatewayTransaction::create([
                'invoice_id' => $invoice->id,
                'payment_channel_id' => $channel->id,
                'external_id' => $externalId,
                'gateway_trx_id' => $result->gatewayTrxId,
                'invoice_url' => $result->invoiceUrl,
                'status' => 'pending',
                'amount' => $sisaTagihan,
                'expired_at' => $result->expiredAt,
                'raw_response' => $result->rawResponse,
            ]);

            // Link lama dibatalkan SETELAH link baru berhasil dibuat, supaya
            // kalau pembuatan link baru gagal, invoice tidak kehilangan link.
            if ($existing) {
                $this->cancelStale($existing);
            }

            return $transaction;
        });
    }

    /**
     * Batalkan link lama. Kalau gateway punya metode expireInvoice(), link
     * di Xendit ikut dimatikan. Kalau tidak (atau gagal), link lama mungkin
     * masih bisa dibayar — itu aman, karena HandleGatewayWebhookAction tetap
     * memproses pembayaran masuk untuk transaksi berstatus 'cancelled'.
     */
    private function cancelStale(PaymentGatewayTransaction $stale): void
    {
        if (method_exists($this->gateway, 'expireInvoice') && $stale->gateway_trx_id) {
            try {
                $this->gateway->expireInvoice($stale->gateway_trx_id);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $stale->update(['status' => 'cancelled']);
    }
}
