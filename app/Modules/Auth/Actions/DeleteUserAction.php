<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUserAction
{
    /**
     * 2 guard bisnis:
     * 1. User tidak boleh hapus akunnya sendiri — mencegah klik tidak
     *    sengaja yang langsung bikin dia ke-logout paksa di tengah kerja.
     * 2. Tidak boleh menghapus user Superadmin terakhir — sama seperti
     *    guard di UpdateUserAction, mencegah sistem lockout total.
     */
    public function execute(User $user, User $actor): void
    {
        if ($user->id === $actor->id) {
            throw ValidationException::withMessages([
                'user' => 'Tidak bisa menghapus akun sendiri.',
            ]);
        }

        if ($user->hasRole('superadmin')) {
            $otherSuperadmins = User::role('superadmin')->where('id', '!=', $user->id)->count();

            if ($otherSuperadmins === 0) {
                throw ValidationException::withMessages([
                    'user' => 'Tidak bisa menghapus akun ini — ini satu-satunya akun Superadmin yang tersisa.',
                ]);
            }
        }

        $user->delete();
    }
}
