<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Collection;
use Modules\Finance\Models\Invoice;

class CalculateComponentBreakdownAction
{
    /**
     * invoice_payments cuma nyimpen 1 nominal per transaksi ke 1 invoice
     * (bukan per item), jadi alokasi ke tiap komponen biaya (SPP,
     * Kegiatan, dll) dihitung pakai aturan FIFO: item recurring (SPP)
     * diprioritaskan lunas duluan, baru sisa pembayaran mengalir ke item
     * non-recurring lain (urut berdasarkan id item). Keputusan ini
     * disengaja — proporsional/pro-rata dianggap ambigu untuk laporan ini.
     */
    public function execute(int $academicYearId, ?int $periodMonth, ?int $periodYear): Collection
    {
        $invoices = Invoice::query()
            ->with(['items.billingType', 'payments'])
            ->where('academic_year_id', $academicYearId)
            ->when($periodMonth, fn ($query) => $query->where('period_month', $periodMonth))
            ->when($periodYear, fn ($query) => $query->where('period_year', $periodYear))
            ->get();

        $allocations = [];

        foreach ($invoices as $invoice) {
            $items = $invoice->items
                ->sortBy(fn ($item) => [$item->billingType->is_recurring ? 0 : 1, $item->id])
                ->map(fn ($item) => (object) [
                    'billing_type_name' => $item->billingType->name,
                    'remaining' => (float) $item->amount,
                ])
                ->values();

            foreach ($invoice->payments->sortBy('paid_at') as $payment) {
                $remainingPayment = (float) $payment->amount_paid;

                foreach ($items as $item) {
                    if ($remainingPayment <= 0) {
                        break;
                    }

                    if ($item->remaining <= 0) {
                        continue;
                    }

                    $allocate = min($remainingPayment, $item->remaining);
                    $item->remaining -= $allocate;
                    $remainingPayment -= $allocate;

                    $allocations[$item->billing_type_name] = ($allocations[$item->billing_type_name] ?? 0) + $allocate;
                }
            }
        }

        return collect($allocations)
            ->map(fn ($total, $name) => ['billing_type_name' => $name, 'total_diterima' => $total])
            ->sortByDesc('total_diterima')
            ->values();
    }
}