<?php

namespace Modules\Finance\Actions;

use Modules\Finance\Models\Invoice;

class GetInvoiceSummaryForStudentAction
{
    public function execute(int $studentId): array
    {
        $outstandingInvoices = Invoice::query()
            ->where('student_id', $studentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->withSum('payments', 'amount_paid')
            ->get();

        return [
            'total_outstanding' => (float) $outstandingInvoices->sum(
                fn ($invoice) => $invoice->total_amount - ($invoice->payments_sum_amount_paid ?? 0)
            ),
            'unpaid_invoice_count' => $outstandingInvoices->count(),
            // Jatuh tempo bersifat opsional. Invoice tanpa jatuh tempo diabaikan di sini,
            // kalau tidak sortBy menaruhnya di urutan pertama dan hasilnya jadi null
            // walaupun invoice lain punya jatuh tempo.
            'next_due_date' => $outstandingInvoices
                ->whereNotNull('due_date')
                ->sortBy('due_date')
                ->first()?->due_date?->toDateString(),
        ];
    }
}
