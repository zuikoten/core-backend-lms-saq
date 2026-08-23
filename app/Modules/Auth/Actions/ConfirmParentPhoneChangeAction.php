<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConfirmParentPhoneChangeAction
{
    public function __construct(private VerifyOtpAction $verifyOtp) {}

    /**
     * Nomor baru diambil dari kolom phone_number milik baris OtpCode yang
     * berhasil diverifikasi — BUKAN dari input ulang di request konfirmasi
     * — supaya nomor yang tersimpan presis nomor yang tadi dikirimi &
     * dibuktikan OTP-nya.
     *
     * Update ke tabel `parents` sengaja lewat DB::table() (bukan import
     * Model dari modul Student) — modul Auth adalah modul fondasi, gak
     * boleh import Model dari modul konsumen (STYLE_GUIDE.md bagian 2).
     *
     * Semua token Sanctum LAIN direvoke begitu sukses ganti nomor — device
     * yang mungkin sudah gak dipegang parent lagi (skenario paling umum:
     * HP lama hilang/dijual) langsung kehilangan akses. Token yang lagi
     * dipakai buat request ini sendiri sengaja TIDAK ikut dihapus, supaya
     * parent gak ke-logout paksa di device yang baru saja dia pakai buat
     * membuktikan diri lewat OTP.
     */
    public function execute(User $user, string $otpCode): User
    {
        $otp = $this->verifyOtp->execute('change_phone', $otpCode, user: $user);

        return DB::transaction(function () use ($user, $otp) {
            $user->update(['phone_number' => $otp->phone_number]);

            DB::table('parents')
                ->where('user_id', $user->id)
                ->update([
                    'phone_number' => $otp->phone_number,
                    'updated_at' => now(),
                ]);

            $currentTokenId = $user->currentAccessToken()?->id;
            $user->tokens()
                ->when($currentTokenId, fn ($q) => $q->where('id', '!=', $currentTokenId))
                ->delete();

            return $user->fresh();
        });
    }
}
