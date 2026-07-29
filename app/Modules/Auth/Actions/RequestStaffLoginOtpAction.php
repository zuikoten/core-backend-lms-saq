<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestStaffLoginOtpAction
{
    public function __construct(private readonly GenerateOtpAction $generateOtpAction)
    {
    }

    /**
     * @param  string  $phoneNumber  Sudah dinormalisasi ke format 62xxxxxxxxxx oleh FormRequest
     */
    public function execute(string $phoneNumber): User
    {
        $user = User::query()->where('phone_number', $phoneNumber)->first();

        if (! $user || ! $user->can('panel.access')) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor HP tidak ditemukan atau bukan akun yang berwenang.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'phone_number' => 'Akun Anda tidak aktif, hubungi pemilik sistem.',
            ]);
        }

        $this->generateOtpAction->execute('login', $phoneNumber, $user);

        return $user;
    }
}