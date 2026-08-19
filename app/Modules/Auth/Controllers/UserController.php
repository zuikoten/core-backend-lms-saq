<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Actions\ListStaffUsersAction;
use Modules\Auth\Actions\CreateUserAction;
use Modules\Auth\Actions\DeleteUserAction;
use Modules\Auth\Actions\UpdateUserAction;
use Modules\Auth\Requests\StoreUserRequest;
use Modules\Auth\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Cuma tampilkan user yang punya role ber-guard 'web' (staf) — user
     * dengan role guard 'sanctum' (parent, dan nanti kemungkinan student)
     * sengaja di-exclude dari halaman ini. Mereka bukan konsumen panel
     * Blade ini, dan role mereka dikelola otomatis lewat alur aktivasi
     * masing-masing (lihat ActivateParentAccountAction), bukan hal yang
     * seharusnya diubah manual lewat form staf ini.
     */
    public function index(Request $request, ListStaffUsersAction $action): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        $users = $action->execute($request->only(['search', 'role']));

        return view('modules.auth.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();

        return view('modules.auth.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('users.index')->with('status', 'User baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
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

    private function currentUser(): ?User
    {
        return Auth::user();
    }
}
