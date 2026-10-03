<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.rbac.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.rbac.roles.create', [
            'role' => new Role(['guard_name' => config('rbac.guard_name', 'web')]),
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', config('rbac.guard_name', 'web')),
            ],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists(config('permission.table_names.permissions'), 'id')],
        ]);

        $role = Role::query()->create([
            'name' => $validated['name'],
            'guard_name' => config('rbac.guard_name', 'web'),
            'description' => $validated['description'] ?? null,
        ]);

        $permissionNames = Permission::query()
            ->whereIn('id', $validated['permissions'] ?? [])
            ->pluck('name')
            ->all();

        $role->syncPermissions($permissionNames);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role created and permissions assigned.');
    }

    public function edit(Role $role): View
    {
        return view('admin.rbac.roles.edit', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->ignore($role->id)
                    ->where('guard_name', config('rbac.guard_name', 'web')),
            ],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists(config('permission.table_names.permissions'), 'id')],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $permissionNames = Permission::query()
            ->whereIn('id', $validated['permissions'] ?? [])
            ->pluck('name')
            ->all();

        $role->syncPermissions($permissionNames);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role updated and permissions synced.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'This role is assigned to users and cannot be deleted yet.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role deleted.');
    }

    protected function permissionGroups(): Collection
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);

        return view('admin.rbac.roles.show', compact('role'));
    }
}
