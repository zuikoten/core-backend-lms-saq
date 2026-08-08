<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\StudentTariffMapping;
use Modules\Student\Models\Student;

class GenerateMonthlyInvoicesAction
{
    public function __construct(
        private GenerateInvoiceNumberAction $generateInvoiceNumber,
    ) {}

    /**
     * Tetap dicek ulang per siswa (bukan percaya hasil preview mentah-mentah)
     * untuk menghindari race condition — siswa yang ternyata sudah kena
     * invoice di antara preview & submit cukup di-skip.
     *
     * @param  array<int>  $studentIds
     * @return array{created: int, skipped: int}
     */
    public function execute(int $academicYearId, int $periodMonth, int $periodYear, ?string $dueDate, array $studentIds, ?int $createdBy): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($studentIds as $studentId) {
            $sudahAdaInvoice = DB::table('invoices')
                ->where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->where('period_month', $periodMonth)
                ->where('period_year', $periodYear)
                ->exists();

            $mappings = StudentTariffMapping::query()
                ->with('billingTariff.billingType')
                ->where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->whereHas('billingTariff.billingType', fn ($query) => $query->where('is_recurring', true))
                ->get();

            if ($sudahAdaInvoice || $mappings->isEmpty()) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($studentId, $academicYearId, $periodMonth, $periodYear, $dueDate, $mappings, $createdBy) {
                $student = Student::findOrFail($studentId);

                $invoice = Invoice::create([
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'created_by' => $createdBy,
                    'invoice_number' => $this->generateInvoiceNumber->execute($student, $periodYear, $periodMonth),
                    'period_month' => $periodMonth,
                    'period_year' => $periodYear,
                    'due_date' => $dueDate,
                    'total_amount' => $mappings->sum(fn ($mapping) => $mapping->billingTariff->amount),
                    'status' => 'unpaid',
                ]);

                foreach ($mappings as $mapping) {
                    $invoice->items()->create([
                        'billing_type_id' => $mapping->billing_type_id,
                        'item_name' => $mapping->billingTariff->billingType->name,
                        'amount' => $mapping->billingTariff->amount,
                    ]);
                }
            });

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}