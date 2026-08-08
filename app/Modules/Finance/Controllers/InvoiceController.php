<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Models\AcademicYear;
use Modules\Finance\Actions\AddInvoiceItemAction;
use Modules\Finance\Actions\CreateManualInvoiceAction;
use Modules\Finance\Actions\DeleteInvoiceAction;
use Modules\Finance\Actions\DeleteInvoiceItemAction;
use Modules\Finance\Actions\FindStudentsForInvoiceGenerationAction;
use Modules\Finance\Actions\GenerateMonthlyInvoicesAction;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\BillingType;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Requests\EligibleStudentsForInvoiceRequest;
use Modules\Finance\Requests\StoreBulkInvoiceRequest;
use Modules\Finance\Requests\StoreInvoiceItemRequest;
use Modules\Finance\Requests\StoreManualInvoiceRequest;
use Modules\Student\Models\Student;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = Invoice::query()
            ->with(['student', 'academicYear'])
            ->latest('id')
            ->paginate(20);

        return view('modules.finance.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['student', 'academicYear', 'createdBy', 'items.billingType']);
        $billingTypes = BillingType::query()->orderBy('name')->get();

        return view('modules.finance.invoices.show', compact('invoice', 'billingTypes'));
    }

    public function bulkCreate(): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();

        return view('modules.finance.invoices.bulk-create', compact('academicYears'));
    }

    public function eligibleStudents(EligibleStudentsForInvoiceRequest $request, FindStudentsForInvoiceGenerationAction $action): JsonResponse
    {
        $students = $action->execute(
            $request->validated('academic_year_id'),
            $request->validated('period_month'),
            $request->validated('period_year'),
            $request->validated('class_group_id'),
        );

        return response()->json(['students' => $students]);
    }

    public function bulkStore(StoreBulkInvoiceRequest $request, GenerateMonthlyInvoicesAction $action): RedirectResponse
    {
        $result = $action->execute(
            $request->validated('academic_year_id'),
            $request->validated('period_month'),
            $request->validated('period_year'),
            $request->validated('due_date'),
            $request->validated('student_ids'),
            auth()->id(),
        );

        $pesan = "{$result['created']} invoice berhasil dibuat";
        $pesan .= $result['skipped'] > 0 ? ", {$result['skipped']} dilewati (sudah ada invoice/tidak ada tarif)." : '.';

        return redirect()->route('finance.invoices.index')->with('status', $pesan);
    }

    public function manualCreate(): View
    {
        $students = Student::query()->orderBy('full_name')->get();
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();
        $billingTariffs = BillingTariff::query()->with('billingType')->get();

        return view('modules.finance.invoices.manual-create', compact('students', 'academicYears', 'billingTariffs'));
    }

    public function manualStore(StoreManualInvoiceRequest $request, CreateManualInvoiceAction $action): RedirectResponse
    {
        $invoice = $action->execute($request->validated(), auth()->id());

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('status', 'Invoice berhasil dibuat.');
    }

    public function destroy(Invoice $invoice, DeleteInvoiceAction $action): RedirectResponse
    {
        $action->execute($invoice);

        return redirect()->route('finance.invoices.index')->with('status', 'Invoice berhasil dihapus.');
    }

    public function storeItem(StoreInvoiceItemRequest $request, Invoice $invoice, AddInvoiceItemAction $action): RedirectResponse
    {
        $action->execute($invoice, $request->validated());

        return redirect()->route('finance.invoices.show', $invoice)->with('status', 'Item berhasil ditambahkan.');
    }

    public function destroyItem(Invoice $invoice, InvoiceItem $item, DeleteInvoiceItemAction $action): RedirectResponse
    {
        $action->execute($item);

        return redirect()->route('finance.invoices.show', $invoice)->with('status', 'Item berhasil dihapus.');
    }
}