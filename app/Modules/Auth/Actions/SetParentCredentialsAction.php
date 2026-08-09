<?php

namespace Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetParentCredentialsAction
{
    public function execute(User $user, string $email, string $password): User
    {
        $user->update([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        return $user;
    }
}