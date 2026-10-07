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

        // Kalau action melempar exception, log tetap processed=false dan
        // Xendit akan retry (response 500) — memang itu yang diinginkan.
        $transaction = $action->execute($request->all());

        // Assign properti langsung (bukan update([...])) supaya tidak
        // tergantung $fillable WebhookLog — kolom relasi ini tidak boleh
        // hilang diam-diam seperti kasus note/approved_by di mapping.
        $log->payment_gateway_transaction_id = $transaction?->id;
        // processed=true hanya kalau callback berhasil dicocokkan ke transaksi;
        // callback tak dikenal (mis. payload tes dari dashboard Xendit) tetap
        // dijawab 200 supaya tidak di-retry, tapi ditandai belum diproses.
        $log->processed = $transaction !== null;
        $log->save();

        return response()->noContent();
    }
}
