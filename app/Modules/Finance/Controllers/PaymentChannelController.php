<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Actions\CreatePaymentChannelAction;
use Modules\Finance\Actions\DeletePaymentChannelAction;
use Modules\Finance\Actions\UpdatePaymentChannelAction;
use Modules\Finance\Models\PaymentChannel;
use Modules\Finance\Requests\StorePaymentChannelRequest;
use Modules\Finance\Requests\UpdatePaymentChannelRequest;

class PaymentChannelController extends Controller
{
    public function index(): View
    {
        $paymentChannels = PaymentChannel::query()->orderBy('name')->paginate(15);

        return view('modules.finance.payment-channels.index', compact('paymentChannels'));
    }

    public function create(): View
    {
        return view('modules.finance.payment-channels.create');
    }

    public function store(StorePaymentChannelRequest $request, CreatePaymentChannelAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('finance.payment-channels.index')
            ->with('status', 'Kanal pembayaran berhasil ditambahkan.');
    }

    public function edit(PaymentChannel $paymentChannel): View
    {
        return view('modules.finance.payment-channels.edit', compact('paymentChannel'));
    }

    public function update(UpdatePaymentChannelRequest $request, PaymentChannel $paymentChannel, UpdatePaymentChannelAction $action): RedirectResponse
    {
        $action->execute($paymentChannel, $request->validated());

        return redirect()
            ->route('finance.payment-channels.index')
            ->with('status', 'Kanal pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentChannel $paymentChannel, DeletePaymentChannelAction $action): RedirectResponse
    {
        $action->execute($paymentChannel);

        return redirect()
            ->route('finance.payment-channels.index')
            ->with('status', 'Kanal pembayaran berhasil dihapus.');
    }
}
