<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Notifications\Contracts\WhatsappGatewayInterface;

class WhatsappWebhookController extends Controller
{
    public function __construct(private readonly WhatsappGatewayInterface $gateway) {}

    /**
     * Diamankan pakai secret token di query string (bukan header/signature
     * -- Fonnte gak dokumentasiin mekanisme signing apa pun buat webhook
     * masuk), dicek manual karena ini bukan endpoint yang di-otentikasi
     * lewat guard Laravel biasa (yang manggil ini server Fonnte, bukan
     * user kita).
     *
     * CATATAN ADAPTABILITAS: beda dari send()/parseStatusWebhook() yang
     * sudah teradaptasi penuh lewat WhatsappGatewayInterface, verifikasi
     * keamanan webhook ini SENGAJA di-hardcode di controller, BUKAN bagian
     * dari interface -- karena baru ada 1 provider (Fonnte) buat dijadikan
     * acuan bentuknya. Kalau provider baru pakai mekanisme beda (mis. HMAC
     * signature di header), controller ini WAJIB disesuaikan manual di titik
     * ini, atau pertimbangkan tambah method `verifyWebhookRequest(Request
     * $request): bool` ke WhatsappGatewayInterface supaya tiap gateway class
     * nentuin caranya sendiri -- didiskusikan pas kejadian, sengaja ditunda
     * sekarang.
     */
    public function handleStatusUpdate(Request $request): JsonResponse
    {
        if (! hash_equals((string) config('services.whatsapp_gateway.webhook_token'), (string) $request->query('token'))) {
            abort(403);
        }

        $status = $this->gateway->parseStatusWebhook($request->all());

        if ($status->messageId === '') {
            return response()->json(['status' => 'ignored']);
        }

        $updated = OtpCode::query()
            ->where('gateway_message_id', $status->messageId)
            ->update(['delivery_status' => $status->state->value]);

        if (! $updated) {
            // Bukan error -- bisa aja webhook ini buat pesan WA lain di
            // luar konteks OTP (kalau gateway dipakai bareng notifikasi
            // lain nanti), atau race condition wajar (webhook lebih cepat
            // sampai daripada gateway_message_id sempat disimpan).
            Log::info('[WhatsappWebhookController] messageId tidak cocok dengan OTP manapun.', [
                'message_id' => $status->messageId,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
