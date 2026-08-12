<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\CreateUserAction;
use Modules\Auth\Actions\DeleteUserAction;
use Modules\Auth\Actions\UpdateUserAction;
use Modules\Auth\Requests\StoreUserRequest;
use Modules\Auth\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->latest()->paginate(15);

        return view('modules.auth.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('modules.auth.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('users.index')->with('status', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('modules.auth.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $action->execute($user, $request->validated());

        return redirect()->route('users.index')->with('status', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user, DeleteUserAction $action): RedirectResponse
    {
        $action->execute($user, $this->currentUser());

        return redirect()->route('users.index')->with('status', 'User berhasil dihapus.');
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
