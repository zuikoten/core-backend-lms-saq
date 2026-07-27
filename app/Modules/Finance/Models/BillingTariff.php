<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\AcademicYear;

class BillingTariff extends Model
{
    protected $fillable = [
        'billing_type_id',
        'academic_year_id',
        'tariff_name',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function billingType(): BelongsTo
    {
        return $this->belongsTo(BillingType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentTariffMappings(): HasMany
    {
        return $this->hasMany(StudentTariffMapping::class);
    }
}
