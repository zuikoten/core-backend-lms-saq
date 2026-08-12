<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Auth\Actions\CreateRoleAction;
use Modules\Auth\Actions\DeleteRoleAction;
use Modules\Auth\Actions\UpdateRoleAction;
use Modules\Auth\Requests\StoreRoleRequest;
use Modules\Auth\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('modules.auth.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissionGroups = $this->groupedPermissions();

        return view('modules.auth.roles.create', compact('permissionGroups'));
    }

    public function store(StoreRoleRequest $request, CreateRoleAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('roles.index')->with('status', 'Role baru berhasil ditambahkan.');
    }

    public function edit(Role $role): View
    {
        $permissionGroups = $this->groupedPermissions();
        $role->load('permissions');

        return view('modules.auth.roles.edit', compact('role', 'permissionGroups'));
    }

    public function update(UpdateRoleRequest $request, Role $role, UpdateRoleAction $action): RedirectResponse
    {
        $action->execute($role, $request->validated());

        return redirect()->route('roles.index')->with('status', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role, DeleteRoleAction $action): RedirectResponse
    {
        $action->execute($role);

        return redirect()->route('roles.index')->with('status', 'Role berhasil dihapus.');
    }

    /**
     * Dikelompokkan per domain (potongan sebelum titik pertama di nama
     * permission, mis. "finance" dari "finance.manage") supaya checkbox di
     * form Role tidak jadi 1 daftar panjang tak terstruktur.
     */
    private function groupedPermissions(): Collection
    {
        return Permission::orderBy('name')->get()->groupBy(
            fn (Permission $permission) => explode('.', $permission->name)[0]
        );
    }
}
