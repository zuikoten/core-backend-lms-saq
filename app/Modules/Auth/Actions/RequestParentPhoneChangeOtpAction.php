<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Models\OtpCode;

class RequestParentPhoneChangeOtpAction
{
    public function __construct(private GenerateOtpAction $generateOtp) {}

    /**
     * OTP dikirim ke NOMOR BARU (bukan nomor lama) — membuktikan parent
     * benar-benar bisa menerima pesan di nomor itu sebelum swap terjadi.
     * Ini mencegah 2 hal sekaligus: salah ketik yang bisa mengunci akun
     * sendiri (nomor HP adalah satu-satunya kanal OTP login buat parent
     * yang belum setCredentials()), dan orang lain iseng ganti nomor
     * milik akun yang bukan miliknya.
     *
     * Nomor yang sudah dipakai user lain sudah ditolak duluan di
     * RequestPhoneChangeOtpRequest (Rule::unique) — di sini cuma cek kasus
     * "nomor baru == nomor lama", yang butuh akses ke $user->phone_number
     * langsung, jadi lebih pas ditaruh di Action.
     */
    public function execute(User $user, string $newPhoneNumber): OtpCode
    {
        if ($newPhoneNumber === $user->phone_number) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor baru sama dengan nomor yang sekarang.',
            ]);
        }

        return $this->generateOtp->execute('change_phone', $newPhoneNumber, $user);
    }
}
