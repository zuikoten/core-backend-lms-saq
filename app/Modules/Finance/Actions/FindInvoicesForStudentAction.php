<?php

namespace Modules\Finance\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\Invoice;

class FindInvoicesForStudentAction
{
    public function execute(int $studentId, ?string $status, ?int $academicYearId): LengthAwarePaginator
    {
        return Invoice::query()
            ->where('student_id', $studentId)
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->when($academicYearId, fn ($query, $academicYearId) => $query->where('academic_year_id', $academicYearId))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(15);
    }
}