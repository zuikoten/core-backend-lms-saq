<?php

namespace Modules\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Finance\Models\Invoice $resource
 */
class InvoiceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paidAmount = $this->resource->payments->sum('amount_paid');

        return [
            'id' => $this->resource->id,
            'invoice_number' => $this->resource->invoice_number,
            'period_month' => $this->resource->period_month,
            'period_year' => $this->resource->period_year,
            'due_date' => $this->resource->due_date?->toDateString(),
            'total_amount' => (float) $this->resource->total_amount,
            'paid_amount' => (float) $paidAmount,
            'remaining_amount' => (float) ($this->resource->total_amount - $paidAmount),
            'status' => $this->resource->status,
            'items' => $this->resource->items->map(fn ($item) => [
                'item_name' => $item->item_name,
                'amount' => (float) $item->amount,
            ]),
            'student' => [
                'id' => $this->resource->student->id,
                'full_name' => $this->resource->student->full_name,
            ],
        ];
    }
}