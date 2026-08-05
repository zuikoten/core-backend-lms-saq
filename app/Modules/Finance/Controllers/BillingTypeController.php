<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Actions\CreateBillingTypeAction;
use Modules\Finance\Actions\DeleteBillingTypeAction;
use Modules\Finance\Actions\UpdateBillingTypeAction;
use Modules\Finance\Models\BillingType;
use Modules\Finance\Requests\StoreBillingTypeRequest;
use Modules\Finance\Requests\UpdateBillingTypeRequest;

class BillingTypeController extends Controller
{
    public function index(): View
    {
        $billingTypes = BillingType::query()->orderBy('name')->paginate(15);

        return view('modules.finance.billing-types.index', compact('billingTypes'));
    }

    public function create(): View
    {
        return view('modules.finance.billing-types.create');
    }

    public function store(StoreBillingTypeRequest $request, CreateBillingTypeAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('finance.billing-types.index')
            ->with('status', 'Jenis tagihan berhasil ditambahkan.');
    }

    public function edit(BillingType $billingType): View
    {
        return view('modules.finance.billing-types.edit', compact('billingType'));
    }

    public function update(UpdateBillingTypeRequest $request, BillingType $billingType, UpdateBillingTypeAction $action): RedirectResponse
    {
        $action->execute($billingType, $request->validated());

        return redirect()
            ->route('finance.billing-types.index')
            ->with('status', 'Jenis tagihan berhasil diperbarui.');
    }

    public function destroy(BillingType $billingType, DeleteBillingTypeAction $action): RedirectResponse
    {
        $action->execute($billingType);

        return redirect()
            ->route('finance.billing-types.index')
            ->with('status', 'Jenis tagihan berhasil dihapus.');
    }
}
