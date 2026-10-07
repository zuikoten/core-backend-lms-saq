<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Invoice;

class DeleteInvoiceAction
{
    /**
     * Invoice tidak boleh dihapus kalau:
     *  1. sudah ada pembayaran (invoice_payments atau transaksi gateway 'paid'), atau
     *  2. masih punya link pembayaran online yang AKTIF (pending/cancelled yang
     *     belum kedaluwarsa, dengan toleransi 1 jam untuk webhook yang telat) —
     *     karena link itu masih bisa dibayar orang tua, dan kalau transaksinya
     *     ikut terhapus, uang yang masuk tidak akan bisa dicocokkan lagi.
     *
     * Transaksi gateway yang sudah expired/failed tidak membawa uang, jadi
     * tidak menghalangi. FK payment_gateway_transactions.invoice_id bersifat
     * RESTRICT, maka baris-baris itu dibersihkan dulu sebelum invoice dihapus
     * (webhook_logs aman: FK-nya ON DELETE SET NULL).
     */
    public function execute(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $sudahDibayar = DB::table('invoice_payments')->where('invoice_id', $locked->id)->exists()
                || DB::table('payment_gateway_transactions')
                    ->where('invoice_id', $locked->id)
                    ->where('status', 'paid')
                    ->exists();

            if ($sudahDibayar) {
                throw ValidationException::withMessages([
                    'invoice_number' => 'Invoice ini sudah ada pembayaran, tidak bisa dihapus.',
                ]);
            }

            $linkMasihAktif = DB::table('payment_gateway_transactions')
                ->where('invoice_id', $locked->id)
                ->whereIn('status', ['pending', 'cancelled'])
                ->where(fn ($query) => $query
                    ->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now()->subHour()))
                ->exists();

            if ($linkMasihAktif) {
                throw ValidationException::withMessages([
                    'invoice_number' => 'Invoice ini masih punya link pembayaran online yang aktif. Tunggu sampai link kedaluwarsa (maksimal 24 jam) sebelum menghapus.',
                ]);
            }

            DB::table('payment_gateway_transactions')->where('invoice_id', $locked->id)->delete();

            $locked->delete();
        });
    }
}
