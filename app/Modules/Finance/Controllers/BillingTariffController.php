<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Models\AcademicYear;
use Modules\Finance\Actions\CreateBillingTariffAction;
use Modules\Finance\Actions\DeleteBillingTariffAction;
use Modules\Finance\Actions\UpdateBillingTariffAction;
use Modules\Finance\Models\BillingTariff;
use Modules\Finance\Models\BillingType;
use Modules\Finance\Requests\StoreBillingTariffRequest;
use Modules\Finance\Requests\UpdateBillingTariffRequest;

class BillingTariffController extends Controller
{
    public function index(): View
    {
        $billingTariffs = BillingTariff::query()
            ->with(['billingType', 'academicYear'])
            ->latest('id')
            ->paginate(15);

        return view('modules.finance.billing-tariffs.index', compact('billingTariffs'));
    }

    public function create(): View
    {
        $billingTypes = BillingType::query()->orderBy('name')->get();
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();

        return view('modules.finance.billing-tariffs.create', compact('billingTypes', 'academicYears'));
    }

    public function store(StoreBillingTariffRequest $request, CreateBillingTariffAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('finance.billing-tariffs.index')
            ->with('status', 'Tarif berhasil ditambahkan.');
    }

    public function edit(BillingTariff $billingTariff): View
    {
        $billingTypes = BillingType::query()->orderBy('name')->get();
        $academicYears = AcademicYear::query()->orderByDesc('year_name')->get();

        return view('modules.finance.billing-tariffs.edit', compact('billingTariff', 'billingTypes', 'academicYears'));
    }

    public function update(UpdateBillingTariffRequest $request, BillingTariff $billingTariff, UpdateBillingTariffAction $action): RedirectResponse
    {
        $action->execute($billingTariff, $request->validated());

        return redirect()
            ->route('finance.billing-tariffs.index')
            ->with('status', 'Tarif berhasil diperbarui.');
    }

    public function destroy(BillingTariff $billingTariff, DeleteBillingTariffAction $action): RedirectResponse
    {
        $action->execute($billingTariff);

        return redirect()
            ->route('finance.billing-tariffs.index')
            ->with('status', 'Tarif berhasil dihapus.');
    }
}