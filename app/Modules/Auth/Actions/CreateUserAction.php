<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    /**
     * Role langsung di-assign lewat syncRoles() (bukan assignRole() satuan)
     * supaya konsisten dengan aturan multi-role per user yang dipakai di
     * seluruh fitur ini — lihat juga UpdateUserAction.
     */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return $user;
    }
}
