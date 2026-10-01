<?php

namespace Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Finance\Actions\HandleGatewayWebhookAction;
use Modules\Finance\Models\WebhookLog;

class XenditWebhookController extends Controller
{
    /**
     * Verifikasi x-callback-token di-hardcode di sini (bukan bagian
     * PaymentGatewayInterface) — sengaja, mengikuti pola
     * WhatsappWebhookController: baru ada 1 provider gateway pembayaran,
     * belum ada acuan pembanding nyata untuk digeneralisasi bentuk
     * verifikasi keamanannya. Dicatat sebagai utang adaptabilitas.
     */
    public function store(Request $request, HandleGatewayWebhookAction $action): Response
    {
        $isValid = hash_equals(
            (string) config('services.xendit.callback_token'),
            (string) $request->header('x-callback-token', ''),
        );

        $log = WebhookLog::create([
            'provider' => 'xendit',
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'signature_valid' => $isValid,
            'processed' => false,
        ]);

        if (! $isValid) {
            return response()->noContent(403);
        }

        $action->execute($request->all());

        $log->update(['processed' => true]);

        return response()->noContent();
    }
}
