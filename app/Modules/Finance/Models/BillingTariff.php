<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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

    /**
     * Nama item invoice yang dibentuk dari tarif — SATU aturan untuk semua jalur
     * (generate massal & form manual). Kalau nama tarif sudah memuat nama jenis
     * tagihan ("SPP Reguler", "Study Tour TK-A (pelita desa)") pakai apa adanya;
     * kalau tidak, gabungkan "Jenis — Tarif". Butuh relasi billingType.
     */
    public function itemLabel(): string
    {
        $typeName = $this->billingType->name;

        return Str::contains(Str::lower($this->tariff_name), Str::lower($typeName))
            ? $this->tariff_name
            : $typeName.' — '.$this->tariff_name;
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
