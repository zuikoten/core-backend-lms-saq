<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Requests\CheckOtpDeliveryStatusRequest;

class OtpDeliveryStatusApiController extends Controller
{
    /**
     * Publik, gak butuh guard -- endpoint ini dipanggil pas user LAGI
     * NUNGGU OTP masuk (termasuk kasus 'activation'/'login' yang emang
     * belum ada sesi login sama sekali). Query by phone_number+action_type
     * (bukan info baru -- itu sudah persis yang React kirim sendiri pas
     * minta OTP), bukan by user_id, biar konsisten dipakai di semua 4
     * skenario termasuk yang belum tentu ada User.
     */
    public function show(CheckOtpDeliveryStatusRequest $request): JsonResponse
    {
        $data = $request->validated();

        $otp = OtpCode::query()
            ->where('phone_number', $data['phone_number'])
            ->where('action_type', $data['action_type'])
            ->latest('created_at')
            ->first();

        return response()->json([
            'delivery_status' => $otp?->delivery_status,
        ]);
    }
}
