<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\AcademicYear;
use Modules\Student\Models\Student;

class Invoice extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'created_by',
        'invoice_number',
        'period_month',
        'period_year',
        'due_date',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function gatewayTransactions(): HasMany
    {
        return $this->hasMany(PaymentGatewayTransaction::class);
    }

    /**
     * Sisa tagihan yang belum dibayar, dihitung on-the-fly
     * (bukan kolom fisik) — sesuai keputusan desain partial payment.
     */
    public function getRemainingBalanceAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->payments()->sum('amount_paid');
    }
}