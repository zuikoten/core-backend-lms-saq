<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;

class GetMonthlyRecapAction
{
    /**
     * total_terbayar dijumlah langsung dari invoice_payments (bukan dari
     * status invoice) supaya invoice 'partial' tetap kehitung nominal yang
     * sudah masuk, bukan dianggap Rp0 sampai lunas penuh.
     *
     * Invoice 'cancelled' tidak ikut tagihan/terbayar/tunggakan.
     *
     * total_kelebihan = uang yang sudah masuk lewat gateway tapi melebihi
     * kebutuhan invoice (payment_gateway_transactions.overpaid_amount). Uang
     * ini TIDAK ada di invoice_payments, jadi tanpa kolom ini rekap akan
     * selisih dengan mutasi/saldo Xendit. Invoice 'cancelled' sengaja ikut
     * dihitung di sini: pembayaran yang masuk setelah invoice dibatalkan
     * seluruhnya adalah kelebihan yang perlu dikembalikan.
     */
    public function execute(int $academicYearId): Collection
    {
        $tagihan = Invoice::query()
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('period_month, period_year, SUM(total_amount) as total_tagihan, COUNT(*) as jumlah_invoice')
            ->groupBy('period_month', 'period_year')
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get();

        $terbayar = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.academic_year_id', $academicYearId)
            ->where('invoices.status', '!=', 'cancelled')
            ->selectRaw('invoices.period_month, invoices.period_year, SUM(invoice_payments.amount_paid) as total_terbayar')
            ->groupBy('invoices.period_month', 'invoices.period_year')
            ->get()
            ->keyBy(fn ($row) => $row->period_year.'-'.$row->period_month);

        $kelebihan = DB::table('payment_gateway_transactions')
            ->join('invoices', 'invoices.id', '=', 'payment_gateway_transactions.invoice_id')
            ->where('invoices.academic_year_id', $academicYearId)
            ->where('payment_gateway_transactions.overpaid_amount', '>', 0)
            ->selectRaw('invoices.period_month, invoices.period_year, SUM(payment_gateway_transactions.overpaid_amount) as total_kelebihan')
            ->groupBy('invoices.period_month', 'invoices.period_year')
            ->get()
            ->keyBy(fn ($row) => $row->period_year.'-'.$row->period_month);

        return $tagihan->map(function ($row) use ($terbayar, $kelebihan) {
            $key = $row->period_year.'-'.$row->period_month;
            $totalTerbayar = (float) ($terbayar[$key]->total_terbayar ?? 0);

            return [
                'period_month' => $row->period_month,
                'period_year' => $row->period_year,
                'total_tagihan' => (float) $row->total_tagihan,
                'total_terbayar' => $totalTerbayar,
                'total_tunggakan' => (float) $row->total_tagihan - $totalTerbayar,
                'total_kelebihan' => (float) ($kelebihan[$key]->total_kelebihan ?? 0),
                'jumlah_invoice' => $row->jumlah_invoice,
            ];
        });
    }
}
