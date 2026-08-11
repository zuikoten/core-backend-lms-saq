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
     */
    public function execute(int $academicYearId): Collection
    {
        $tagihan = Invoice::query()
            ->where('academic_year_id', $academicYearId)
            ->selectRaw('period_month, period_year, SUM(total_amount) as total_tagihan, COUNT(*) as jumlah_invoice')
            ->groupBy('period_month', 'period_year')
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get();

        $terbayar = DB::table('invoice_payments')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.academic_year_id', $academicYearId)
            ->selectRaw('invoices.period_month, invoices.period_year, SUM(invoice_payments.amount_paid) as total_terbayar')
            ->groupBy('invoices.period_month', 'invoices.period_year')
            ->get()
            ->keyBy(fn ($row) => $row->period_year.'-'.$row->period_month);

        return $tagihan->map(function ($row) use ($terbayar) {
            $key = $row->period_year.'-'.$row->period_month;
            $totalTerbayar = (float) ($terbayar[$key]->total_terbayar ?? 0);

            return [
                'period_month' => $row->period_month,
                'period_year' => $row->period_year,
                'total_tagihan' => (float) $row->total_tagihan,
                'total_terbayar' => $totalTerbayar,
                'total_tunggakan' => (float) $row->total_tagihan - $totalTerbayar,
                'jumlah_invoice' => $row->jumlah_invoice,
            ];
        });
    }
}