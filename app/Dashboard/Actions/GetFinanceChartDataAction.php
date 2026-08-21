<?php

namespace App\Dashboard\Actions;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class GetFinanceChartDataAction
{
    /**
     * Data untuk 2 visual: tren pemasukan N bulan terakhir (termasuk bulan
     * yang nilainya nol, supaya sumbu waktu tetap kontinu) & breakdown
     * pemasukan per kanal pembayaran bulan berjalan.
     */
    public function execute(int $bulanTerakhir = 6): array
    {
        $mulai = now()->copy()->subMonths($bulanTerakhir - 1)->startOfMonth();

        $rows = DB::table('invoice_payments')
            ->where('paid_at', '>=', $mulai)
            ->selectRaw('YEAR(paid_at) as tahun, MONTH(paid_at) as bulan, SUM(amount_paid) as total')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn ($row) => $row->tahun.'-'.$row->bulan);

        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($mulai, '1 month', now()) as $bulanIter) {
            $key = $bulanIter->year.'-'.$bulanIter->month;
            $labels[] = $bulanIter->translatedFormat('M Y');
            $values[] = (int) ($rows[$key]->total ?? 0);
        }

        $perKanal = DB::table('invoice_payments')
            ->join('payment_channels', 'payment_channels.id', '=', 'invoice_payments.payment_channel_id')
            ->whereYear('invoice_payments.paid_at', now()->year)
            ->whereMonth('invoice_payments.paid_at', now()->month)
            ->selectRaw('payment_channels.name as nama_kanal, SUM(invoice_payments.amount_paid) as total')
            ->groupBy('payment_channels.name')
            ->orderByDesc('total')
            ->get();

        return [
            'tren_bulanan' => [
                'labels' => $labels,
                'values' => $values,
            ],
            'per_kanal' => [
                'labels' => $perKanal->pluck('nama_kanal')->toArray(),
                'values' => $perKanal->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
            ],
        ];
    }
}
