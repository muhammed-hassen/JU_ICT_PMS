<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('module')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.rbac.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('admin.rbac.permissions.create', [
            'permission' => new Permission(['guard_name' => config('rbac.guard_name', 'web')]),
            'modules' => config('rbac.modules', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique(config('permission.table_names.permissions'), 'name')
                    ->where('guard_name', config('rbac.guard_name', 'web')),
            ],
            'module' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        Permission::query()->create([
            'name' => $validated['name'],
            'guard_name' => config('rbac.guard_name', 'web'),
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission created.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.rbac.permissions.edit', [
            'permission' => $permission->load('roles'),
            'modules' => config('rbac.modules', []),
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique(config('permission.table_names.permissions'), 'name')
                    ->ignore($permission->id)
                    ->where('guard_name', config('rbac.guard_name', 'web')),
            ],
            'module' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update([
            'name' => $validated['name'],
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if ($permission->roles()->exists() || $permission->users()->exists()) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'This permission is already assigned and cannot be deleted yet.');
        }

        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission deleted.');
    }
}
