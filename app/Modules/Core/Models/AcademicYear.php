<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\Invoice;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function billingTariffs(): HasMany
    {
        return $this->hasMany(BillingTariff::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}