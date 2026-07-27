<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\StudentTariffMapping;

class Student extends Model
{
    protected $fillable = [
        'parent_id',
        'nisn',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function tariffMappings(): HasMany
    {
        return $this->hasMany(StudentTariffMapping::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}