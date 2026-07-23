<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingType extends Model
{
    protected $fillable = [
        'name',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
        ];
    }

    public function billingTariffs(): HasMany
    {
        return $this->hasMany(BillingTariff::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}