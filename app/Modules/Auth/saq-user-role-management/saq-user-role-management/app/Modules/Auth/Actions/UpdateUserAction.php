<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdateUserAction
{
    /**
     * Password cuma diupdate kalau field-nya diisi — form edit tidak wajib
     * ganti password tiap kali update data lain.
     *
     * Guard bisnis: kalau user ini sedang jadi satu-satunya pemegang role
     * "Superadmin" di sistem, role itu tidak boleh dilepas dari dia lewat
     * form ini — mencegah sistem kehilangan akun Superadmin sama sekali
     * (lockout total, tidak ada yang bisa lagi kelola Role & Permission).
     */
    public function execute(User $user, array $data): User
    {
        $newRoles = $data['roles'] ?? [];

        if ($user->hasRole('Superadmin') && ! in_array('Superadmin', $newRoles, true)) {
            $otherSuperadmins = User::role('Superadmin')->where('id', '!=', $user->id)->count();

            if ($otherSuperadmins === 0) {
                throw ValidationException::withMessages([
                    'roles' => 'Tidak bisa melepas role Superadmin dari user ini — ini satu-satunya akun Superadmin yang tersisa.',
                ]);
            }
        }

        $user->update([
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'],
            'is_active' => $data['is_active'] ?? $user->is_active,
            ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->syncRoles($newRoles);

        return $user;
    }
}
