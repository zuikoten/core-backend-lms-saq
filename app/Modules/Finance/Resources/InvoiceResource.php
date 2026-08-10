<?php

namespace Modules\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Finance\Models\Invoice $resource
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'invoice_number' => $this->resource->invoice_number,
            'period_month' => $this->resource->period_month,
            'period_year' => $this->resource->period_year,
            'due_date' => $this->resource->due_date?->toDateString(),
            'total_amount' => (float) $this->resource->total_amount,
            'status' => $this->resource->status,
        ];
    }
}