<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Academic\Models\ClassGroup;
use Modules\Core\Models\AcademicYear;
use Modules\Finance\Actions\CalculateComponentBreakdownAction;
use Modules\Finance\Actions\GetMonthlyRecapAction;
use Modules\Finance\Actions\GetOutstandingInvoicesAction;
use Modules\Finance\Actions\GetPaymentChannelRecapAction;
use Modules\Finance\Requests\ComponentBreakdownReportRequest;
use Modules\Finance\Requests\MonthlyRecapReportRequest;
use Modules\Finance\Requests\OutstandingInvoiceReportRequest;
use Modules\Finance\Requests\PaymentChannelRecapReportRequest;

class FinancialReportController extends Controller
{
    public function index(): View
    {
        return view('modules.finance.reports.index');
    }

    public function monthlyRecap(MonthlyRecapReportRequest $request, GetMonthlyRecapAction $action): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();
        $academicYearId = $request->validated('academic_year_id') ?? $academicYears->firstWhere('is_active', true)?->id;

        $recap = $academicYearId ? $action->execute($academicYearId) : collect();

        return view('modules.finance.reports.monthly-recap', compact('academicYears', 'academicYearId', 'recap'));
    }

    public function outstanding(OutstandingInvoiceReportRequest $request, GetOutstandingInvoicesAction $action): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();
        $classGroups = ClassGroup::query()->with(['gradeLevel', 'academicYear'])->orderByDesc('academic_year_id')->get();
        $academicYearId = $request->validated('academic_year_id') ?? $academicYears->firstWhere('is_active', true)?->id;
        $classGroupId = $request->validated('class_group_id');

        $outstandingInvoices = $academicYearId ? $action->execute($academicYearId, $classGroupId) : collect();

        return view('modules.finance.reports.outstanding', compact('academicYears', 'classGroups', 'academicYearId', 'classGroupId', 'outstandingInvoices'));
    }

    public function paymentChannelRecap(PaymentChannelRecapReportRequest $request, GetPaymentChannelRecapAction $action): View
    {
        $dateFrom = $request->validated('date_from');
        $dateTo = $request->validated('date_to');

        $recap = $action->execute($dateFrom, $dateTo);

        return view('modules.finance.reports.payment-channel-recap', compact('recap', 'dateFrom', 'dateTo'));
    }

    public function componentBreakdown(ComponentBreakdownReportRequest $request, CalculateComponentBreakdownAction $action): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();
        $academicYearId = $request->validated('academic_year_id') ?? $academicYears->firstWhere('is_active', true)?->id;
        $periodMonth = $request->validated('period_month');
        $periodYear = $request->validated('period_year');

        $breakdown = $academicYearId ? $action->execute($academicYearId, $periodMonth, $periodYear) : collect();

        return view('modules.finance.reports.component-breakdown', compact('academicYears', 'academicYearId', 'periodMonth', 'periodYear', 'breakdown'));
    }
}
