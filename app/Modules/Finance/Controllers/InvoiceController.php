<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\AcademicYear;
use Modules\Finance\Actions\AddInvoiceItemAction;
use Modules\Finance\Actions\CreateManualInvoiceAction;
use Modules\Finance\Actions\DeleteInvoiceAction;
use Modules\Finance\Actions\DeleteInvoiceItemAction;
use Modules\Finance\Actions\FindStudentsForInvoiceGenerationAction;
use Modules\Finance\Actions\GenerateMonthlyInvoicesAction;
use Modules\Finance\Actions\CreateInvoicePaymentAction;
use Modules\Finance\Actions\DeleteInvoicePaymentAction;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\BillingType;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Models\InvoicePayment;
use Modules\Finance\Models\PaymentChannel;
use Modules\Finance\Requests\EligibleStudentsForInvoiceRequest;
use Modules\Finance\Requests\StoreBulkInvoiceRequest;
use Modules\Finance\Requests\StoreInvoiceItemRequest;
use Modules\Finance\Requests\StoreManualInvoiceRequest;
use Modules\Finance\Requests\StoreInvoicePaymentRequest;
use Modules\Student\Models\Student;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['student', 'academicYear'])
            // Total kelebihan bayar per invoice (untuk badge di daftar).
            ->withSum(
                ['gatewayTransactions as overpaid_total' => fn ($query) => $query->where('overpaid_amount', '>', 0)],
                'overpaid_amount'
            )
            // ?kelebihan=1 → hanya invoice yang punya kelebihan bayar.
            ->when(
                $request->boolean('kelebihan'),
                fn ($query) => $query->whereHas('gatewayTransactions', fn ($t) => $t->overpaid())
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('modules.finance.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['student', 'academicYear', 'createdBy', 'items.billingType', 'payments.paymentChannel', 'payments.handoverBy']);
        $billingTypes = BillingType::query()->orderBy('name')->get();
        $paymentChannels = PaymentChannel::query()->where('is_active', true)->orderBy('name')->get();

        $totalPaid = $invoice->payments->sum('amount_paid');
        $remaining = $invoice->total_amount - $totalPaid;

        $overpaidTransactions = $invoice->gatewayTransactions()->overpaid()->latest('id')->get();
        $latestGatewayTransaction = $invoice->gatewayTransactions()->latest('id')->first();

        return view('modules.finance.invoices.show', compact('invoice', 'billingTypes', 'paymentChannels', 'totalPaid', 'remaining', 'overpaidTransactions', 'latestGatewayTransaction'));
    }

    public function bulkCreate(): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();

        return view('modules.finance.invoices.bulk-create', compact('academicYears'));
    }

    public function eligibleStudents(EligibleStudentsForInvoiceRequest $request, FindStudentsForInvoiceGenerationAction $action): JsonResponse
    {
        $args = [
            $request->validated('academic_year_id'),
            $request->validated('period_month'),
            $request->validated('period_year'),
            $request->validated('class_group_id'),
        ];

        return response()->json([
            'students' => $action->execute(...$args),
            // Siswa aktif yang TIDAK akan ikut digenerate karena belum punya tarif recurring.
            'unmapped' => $action->withoutTariff(...$args),
        ]);
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
        $pesan .= $result['skipped'] > 0 ? ", {$result['skipped']} dilewati (sudah ada invoice/tidak ada tarif/tidak aktif)." : '.';

        return redirect()->route('finance.invoices.index')->with('status', $pesan);
    }

    public function manualCreate(): View
    {
        $students = Student::query()->orderBy('full_name')->get();
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();
        $billingTariffs = BillingTariff::query()->with('billingType')->get();
        $billingTypes = BillingType::query()->orderBy('name')->get();

        return view('modules.finance.invoices.manual-create', compact('students', 'academicYears', 'billingTariffs', 'billingTypes'));
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

        public function storePayment(StoreInvoicePaymentRequest $request, Invoice $invoice, CreateInvoicePaymentAction $action): RedirectResponse
    {
        $action->execute($invoice, $request->validated());

        return redirect()->route('finance.invoices.show', $invoice)->with('status', 'Pembayaran berhasil dicatat.');
    }

    public function destroyPayment(Invoice $invoice, InvoicePayment $payment, DeleteInvoicePaymentAction $action): RedirectResponse
    {
        $action->execute($payment);

        return redirect()->route('finance.invoices.show', $invoice)->with('status', 'Pembayaran berhasil dihapus.');
    }
}
