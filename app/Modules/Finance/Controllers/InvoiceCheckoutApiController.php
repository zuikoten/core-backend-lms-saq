<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finance\Actions\CreateGatewayInvoiceAction;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Resources\PaymentGatewayTransactionResource;
use Modules\Student\Models\ParentProfile;

/**
 * Scope kepemilikan invoice SELALU dibatasi ke parent_id milik user yang
 * login lewat ParentProfile::user_id — pola sama dengan InvoiceApiController.
 */
class InvoiceCheckoutApiController extends Controller
{
    public function store(Request $request, Invoice $invoice, CreateGatewayInvoiceAction $action): PaymentGatewayTransactionResource
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->with('students')
            ->firstOrFail();

        if (! $parentProfile->students->contains('id', $invoice->student_id)) {
            abort(403, 'Anda tidak punya akses ke invoice ini.');
        }

        return new PaymentGatewayTransactionResource($action->execute($invoice));
    }
}
