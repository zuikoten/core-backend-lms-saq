<?php

namespace Modules\Auth\Actions;

use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DeleteRoleAction
{
    /**
     * 2 guard bisnis: Role Superadmin tidak boleh dihapus sama sekali, dan
     * Role apa pun yang masih dipakai user aktif ditolak dulu — user yang
     * kehilangan seluruh role-nya secara diam-diam (karena Role-nya lenyap)
     * gampang berujung dia tidak bisa akses apa-apa tanpa pesan error yang
     * jelas kenapa.
     */
    public function execute(Role $role): void
    {
        if ($role->name === 'superadmin') {
            throw ValidationException::withMessages([
                'role' => 'Role Superadmin tidak bisa dihapus.',
            ]);
        }

        $userCount = $role->users()->count();

        if ($userCount > 0) {
            throw ValidationException::withMessages([
                'role' => "Role ini masih dipakai {$userCount} user — lepas dulu dari semua user sebelum dihapus.",
            ]);
        }

        $role->delete();
    }
}
