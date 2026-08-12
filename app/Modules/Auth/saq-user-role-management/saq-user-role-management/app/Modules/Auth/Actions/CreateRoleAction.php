<?php

namespace Modules\Auth\Actions;

use Spatie\Permission\Models\Role;

class CreateRoleAction
{
    /**
     * guard_name dikunci ke 'web' — Role yang dibuat lewat halaman staf ini
     * memang khusus buat sisi staf (panel Blade), bukan guard 'sanctum'
     * (orang tua, yang cuma punya role 'parent' bawaan sistem).
     */
    public function execute(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }
}
