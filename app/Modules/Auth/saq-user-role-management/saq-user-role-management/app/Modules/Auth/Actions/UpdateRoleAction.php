<?php

namespace Modules\Auth\Actions;

use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateRoleAction
{
    /**
     * Role "Superadmin" dikunci total lewat UI ini (nama maupun daftar
     * permission-nya) — Superadmin tetap bypass semua permission lewat
     * Gate::before di AuthModuleServiceProvider apa pun isi baris
     * role_has_permissions-nya, jadi mengubahnya lewat sini sebenarnya
     * tidak berefek ke akses nyata, tapi tetap diblok supaya UI tidak
     * menyesatkan (seolah bisa diatur padahal percuma) dan supaya nama
     * "Superadmin" tidak bisa diganti jadi nama lain untuk membuat celah.
     */
    public function execute(Role $role, array $data): Role
    {
        if ($role->name === 'Superadmin') {
            throw ValidationException::withMessages([
                'role' => 'Role Superadmin tidak bisa diubah lewat halaman ini.',
            ]);
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }
}
