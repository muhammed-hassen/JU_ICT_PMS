@extends('layouts.console')

@section('title', 'Roles')

@section('content_header')
    <x-ui.page-header title="Roles" description="Manage roles and the permissions each one grants.">
        @canvisit(route('admin.roles.create'))
            <x-ui.button :href="route('admin.roles.create')">
                <i data-lucide="plus" class="size-4"></i>
                New role
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    @php
        // Count across every role, not only the ones on this page.
        $assignedPermissions = \Illuminate\Support\Facades\DB::table(config('permission.table_names.role_has_permissions'))->count();
        $usersWithRoles = \Illuminate\Support\Facades\DB::table(config('permission.table_names.model_has_roles'))->distinct()->count('model_id');
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total roles" :value="$roles->total()" icon="shield" />
        <x-ui.stat-card label="Permissions assigned" :value="$assignedPermissions" icon="key-round" />
        <x-ui.stat-card label="Users with roles" :value="$usersWithRoles" icon="users" />
    </div>

    <x-ui.card flush>
        <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 mr-auto font-sans text-[15px] font-semibold">Role Catalog</h3>
            <div class="relative w-full sm:w-72">
                <label for="role-search" class="sr-only">Search roles</label>
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="role-search" size="sm" type="search" class="pl-9" placeholder="Search roles" autocomplete="off" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="roles-table">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        <x-ui.th class="w-12">#</x-ui.th>
                        <x-ui.th>Role</x-ui.th>
                        <x-ui.th>Description</x-ui.th>
                        <x-ui.th class="text-right">Permissions</x-ui.th>
                        <x-ui.th class="text-right">Users</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $index => $role)
                        <tr class="border-b border-border/70 last:border-0 hover:bg-background" data-row>
                            <x-ui.td class="tabular text-muted-foreground">{{ $roles->firstItem() + $index }}</x-ui.td>
                            <x-ui.td class="min-w-48">
                                <a href="{{ route('admin.roles.show', $role) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                    {{ $role->name }}
                                </a>
                                <p class="m-0 text-[12px] text-muted-foreground">Guard: {{ $role->guard_name }}</p>
                            </x-ui.td>
                            <x-ui.td class="text-[13px] text-muted-foreground">{{ $role->description ?: 'No description' }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $role->permissions_count }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $role->users_count }}</x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5">
                                    @canvisit(route('admin.roles.show', $role))
                                        <x-ui.icon-button icon="eye" label="View role" :href="route('admin.roles.show', $role)" />
                                    @endcanvisit
                                    @canvisit(route('admin.roles.edit', $role))
                                        <x-ui.icon-button icon="pencil" label="Edit role" :href="route('admin.roles.edit', $role)" />
                                    @endcanvisit
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="m-0"
                                          data-confirm="Delete the role &quot;{{ $role->name }}&quot;? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        @if ($role->users_count > 0)
                                            <x-ui.icon-button type="submit" icon="trash-2" :label="'Cannot delete: assigned to ' . $role->users_count . ' user(s)'" tone="destructive" class="disabled:cursor-not-allowed disabled:opacity-40" disabled />
                                        @else
                                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete role" tone="destructive" />
                                        @endif
                                    </form>
                                </div>
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="shield" title="No roles found" description="Create your first role to get started.">
                                    @canvisit(route('admin.roles.create'))
                                        <x-ui.button :href="route('admin.roles.create')">
                                            <i data-lucide="plus" class="size-4"></i>
                                            Create role
                                        </x-ui.button>
                                    @endcanvisit
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="roles-no-match" hidden>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-muted-foreground">No roles on this page match your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <x-slot:footer>
                <span class="tabular text-muted-foreground">Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }} of {{ $roles->total() }}</span>
                <div>{{ $roles->appends(request()->query())->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection

@push('js')
<script>
    (function () {
        var input = document.getElementById('role-search');
        var rows = document.querySelectorAll('#roles-table tbody tr[data-row]');
        var none = document.getElementById('roles-no-match');
        if (input) {
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                var shown = 0;
                rows.forEach(function (row) {
                    var match = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                    row.hidden = !match;
                    if (match) shown++;
                });
                none.hidden = !(rows.length && shown === 0);
            });
        }
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!confirm(form.dataset.confirm)) e.preventDefault();
            });
        });
    })();
</script>
@endpush
