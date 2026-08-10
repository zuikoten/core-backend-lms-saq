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
            'next_due_date' => $outstandingInvoices->sortBy('due_date')->first()?->due_date?->toDateString(),
        ];
    }
}