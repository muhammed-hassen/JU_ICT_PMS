@extends('layouts.console')

@section('title', 'Permissions')

@section('content_header')
    <x-ui.page-header title="Permissions" description="Every permission a role can grant, grouped by module.">
        @canvisit(route('admin.permissions.create'))
            <x-ui.button :href="route('admin.permissions.create')">
                <i data-lucide="plus" class="size-4"></i>
                New permission
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    @php
        // Count across the whole catalog, not only the permissions on this page.
        $moduleCount = \App\Models\Permission::query()->distinct()->count('module');
        $roleAssignments = \Illuminate\Support\Facades\DB::table(config('permission.table_names.role_has_permissions'))->count();
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total permissions" :value="$permissions->total()" icon="key-round" />
        <x-ui.stat-card label="Modules" :value="$moduleCount" icon="layers" />
        <x-ui.stat-card label="Role assignments" :value="$roleAssignments" icon="shield" />
    </div>

    <x-ui.card flush>
        <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 mr-auto font-sans text-[15px] font-semibold">Permission Catalog</h3>
            <div class="relative w-full sm:w-72">
                <label for="permission-search" class="sr-only">Search permissions</label>
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="permission-search" size="sm" type="search" class="pl-9" placeholder="Search permissions" autocomplete="off" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="permissions-table">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        <x-ui.th class="w-12">#</x-ui.th>
                        <x-ui.th>Permission</x-ui.th>
                        <x-ui.th>Module</x-ui.th>
                        <x-ui.th>Description</x-ui.th>
                        <x-ui.th class="text-right">Roles</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $index => $permission)
                        <tr class="border-b border-border/70 last:border-0 hover:bg-background" data-row>
                            <x-ui.td class="tabular text-muted-foreground">{{ $permissions->firstItem() + $index }}</x-ui.td>
                            <x-ui.td class="whitespace-nowrap">
                                <a href="{{ route('admin.permissions.show', $permission) }}" class="font-mono text-[13px] font-medium text-foreground no-underline hover:text-primary hover:no-underline">
                                    {{ $permission->name }}
                                </a>
                            </x-ui.td>
                            <x-ui.td><x-ui.badge>{{ ucfirst($permission->module) }}</x-ui.badge></x-ui.td>
                            <x-ui.td class="text-[13px] text-muted-foreground">{{ $permission->description ?: 'No description' }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $permission->roles_count }}</x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5">
                                    @canvisit(route('admin.permissions.show', $permission))
                                        <x-ui.icon-button icon="eye" label="View permission" :href="route('admin.permissions.show', $permission)" />
                                    @endcanvisit
                                    @canvisit(route('admin.permissions.edit', $permission))
                                        <x-ui.icon-button icon="pencil" label="Edit permission" :href="route('admin.permissions.edit', $permission)" />
                                    @endcanvisit
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="m-0"
                                          data-confirm="Delete the permission &quot;{{ $permission->name }}&quot;? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        @if ($permission->roles_count > 0)
                                            <x-ui.icon-button type="submit" icon="trash-2" :label="'Cannot delete: assigned to ' . $permission->roles_count . ' role(s)'" tone="destructive" class="disabled:cursor-not-allowed disabled:opacity-40" disabled />
                                        @else
                                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete permission" tone="destructive" />
                                        @endif
                                    </form>
                                </div>
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="key-round" title="No permissions found" description="Create your first permission to get started.">
                                    @canvisit(route('admin.permissions.create'))
                                        <x-ui.button :href="route('admin.permissions.create')">
                                            <i data-lucide="plus" class="size-4"></i>
                                            Create permission
                                        </x-ui.button>
                                    @endcanvisit
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="permissions-no-match" hidden>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-muted-foreground">No permissions on this page match your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($permissions->hasPages())
            <x-slot:footer>
                <span class="tabular text-muted-foreground">Showing {{ $permissions->firstItem() }} to {{ $permissions->lastItem() }} of {{ $permissions->total() }}</span>
                <div>{{ $permissions->appends(request()->query())->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection

@push('js')
<script>
    (function () {
        var input = document.getElementById('permission-search');
        var rows = document.querySelectorAll('#permissions-table tbody tr[data-row]');
        var none = document.getElementById('permissions-no-match');
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
