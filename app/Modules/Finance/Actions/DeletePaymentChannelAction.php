<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\PaymentChannel;

class DeletePaymentChannelAction
{
    /**
     * Kanal pembayaran yang sudah dipakai di riwayat transaksi
     * (invoice_payments atau payment_gateway_transactions) tidak boleh
     * dihapus — riwayat pembayaran harus tetap bisa menunjuk ke kanal yang
     * dipakai saat transaksi itu terjadi. Kalau kanal sudah tidak dipakai
     * lagi tapi masih ingin disembunyikan dari pilihan baru, nonaktifkan
     * lewat `is_active`, jangan dihapus.
     */
    public function execute(PaymentChannel $paymentChannel): void
    {
        $dipakaiDiPembayaran = DB::table('invoice_payments')
            ->where('payment_channel_id', $paymentChannel->id)
            ->exists();

        $dipakaiDiTransaksiGateway = DB::table('payment_gateway_transactions')
            ->where('payment_channel_id', $paymentChannel->id)
            ->exists();

        if ($dipakaiDiPembayaran || $dipakaiDiTransaksiGateway) {
            throw ValidationException::withMessages([
                'name' => 'Kanal pembayaran ini masih dipakai di riwayat transaksi, tidak bisa dihapus. Nonaktifkan saja lewat status Aktif.',
            ]);
        }

        $paymentChannel->delete();
    }
}
