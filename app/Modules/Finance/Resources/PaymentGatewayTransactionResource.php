<?php

namespace Modules\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\Finance\Models\PaymentGatewayTransaction $resource
 */
class PaymentGatewayTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'invoice_id' => $this->resource->invoice_id,
            'invoice_url' => $this->resource->invoice_url,
            'status' => $this->resource->status,
            'amount' => $this->resource->amount,
            'expired_at' => $this->resource->expired_at,
        ];
    }
}
