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
     * dibuktikan OTP-nya, bukan nilai baru yang bisa saja beda kalau
     * client kirim payload berbeda di step ini.
     *
     * Update ke tabel `parents` sengaja lewat DB::table() (bukan import
     * Model dari modul Student) — modul Auth adalah modul fondasi, gak
     * boleh import Model dari modul konsumen (STYLE_GUIDE.md bagian 2,
     * "Arah dependency SATU ARAH").
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

            return $user->fresh();
        });
    }
}
