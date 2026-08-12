<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Student\Models\ParentProfile;

class ActivateParentAccountAction
{
    /**
     * Dipanggil SETELAH VerifyOtpAction sukses untuk action_type = activation.
     *
     * @param  string  $phoneNumber  Nomor yang sudah diverifikasi via OTP
     * @param  string|null  $password  Opsional — kalau null, parent murni OTP-only
     *                                  sampai dia set password sendiri nanti.
     */
    public function execute(string $phoneNumber, ?string $password = null): array
{
    $parentProfile = ParentProfile::query()
        ->where('phone_number', $phoneNumber)
        ->whereNull('user_id')
        ->first();

    if (! $parentProfile) {
        throw ValidationException::withMessages([
            'phone_number' => 'Data orang tua tidak ditemukan, hubungi pihak sekolah.',
        ]);
    }

    return DB::transaction(function () use ($parentProfile, $phoneNumber, $password) {
        $user = User::create([
            'phone_number' => $phoneNumber,
            'password' => $password ? Hash::make($password) : null,
            'is_active' => true,
        ]);

        $parentProfile->update(['user_id' => $user->id]);

        $user->assignRole(\Spatie\Permission\Models\Role::findByName('parent', 'sanctum'));

        $token = $user->createToken('parent-app');

        return ['user' => $user, 'token' => $token];
    });
}
}
