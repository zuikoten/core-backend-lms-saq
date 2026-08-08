<?php

namespace Modules\Finance\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finance\Models\Invoice;
use Modules\Student\Models\Student;

class CreateManualInvoiceAction
{
    public function __construct(
        private GenerateInvoiceNumberAction $generateInvoiceNumber,
    ) {}

    /**
     * Kalau siswa sudah punya invoice utk periode yang sama (mis. dari
     * generate massal SPP bulan ini), TIDAK bikin invoice baru — staf
     * diarahkan nambah item ke invoice yang sudah ada, supaya aturan
     * "1 siswa = 1 invoice per bulan" tetap konsisten.
     */
    public function execute(array $data, ?int $createdBy): Invoice
    {
        $sudahAdaInvoice = DB::table('invoices')
            ->where('student_id', $data['student_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('period_month', $data['period_month'])
            ->where('period_year', $data['period_year'])
            ->exists();

        if ($sudahAdaInvoice) {
            throw ValidationException::withMessages([
                'student_id' => 'Siswa ini sudah punya invoice untuk periode yang sama. Tambahkan item lewat halaman invoice yang sudah ada, jangan buat baru.',
            ]);
        }

        return DB::transaction(function () use ($data, $createdBy) {
            $student = Student::findOrFail($data['student_id']);

            $invoice = Invoice::create([
                'student_id' => $data['student_id'],
                'academic_year_id' => $data['academic_year_id'],
                'created_by' => $createdBy,
                'invoice_number' => $this->generateInvoiceNumber->execute($student, $data['period_year'], $data['period_month']),
                'period_month' => $data['period_month'],
                'period_year' => $data['period_year'],
                'due_date' => $data['due_date'] ?? null,
                'total_amount' => collect($data['items'])->sum('amount'),
                'status' => 'unpaid',
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create($item);
            }

            return $invoice;
        });
    }
}