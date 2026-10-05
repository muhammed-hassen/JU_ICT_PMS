{{-- System Administrator dashboard: accounts, roles and what changed recently. --}}
@extends('layouts.console')
@section('title', 'Dashboard')

@section('content_header')
    <x-ui.page-header title="System dashboard" description="Accounts, roles and recent activity.">
        <x-ui.button variant="outline" :href="route('admin.roles.index')">
            <i data-lucide="shield" class="size-4"></i>
            Roles
        </x-ui.button>
        <x-ui.button :href="route('users.create')">
            <i data-lucide="user-plus" class="size-4"></i>
            Add user
        </x-ui.button>
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Users" :value="$usersCount" icon="users" :href="route('users.index')" link-text="Manage users" />
        <x-ui.stat-card label="Roles" :value="$rolesCount" icon="shield" :href="route('admin.roles.index')" link-text="Manage roles" />
        <x-ui.stat-card label="Permissions" :value="$permissionsCount" icon="key-round" :href="route('admin.permissions.index')" link-text="View permissions" />
        <x-ui.stat-card label="Users without a role" :value="$usersWithoutRole" icon="user-x" :hint="$usersWithoutRole ? 'They cannot open any page until given a role.' : 'Every account has a role.'" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">
        <x-ui.card title="Users per role" :description="$teamsCount . ' teams in the organization.'" flush>
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($roles as $role)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="font-medium text-foreground">{{ $role->name }}</span>
                        <span class="tabular font-semibold">{{ $role->users_count }}</span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card class="lg:col-span-2" title="Newest accounts" flush>
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($recentUsers as $account)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <span class="block truncate font-semibold text-foreground">{{ $account->name }}</span>
                            <span class="block truncate text-[13px] text-muted-foreground">{{ $account->email }}</span>
                        </div>
                        <x-ui.badge :variant="$account->roles->isEmpty() ? 'warning' : 'neutral'">{{ $account->roles->pluck('name')->first() ?? 'No role' }}</x-ui.badge>
                        <span class="hidden shrink-0 text-xs text-muted-foreground sm:inline">{{ $account->created_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Recent activity" description="The latest changes recorded in the activity log." flush>
        @if ($recentActivity->isEmpty())
            <x-ui.empty-state icon="history" title="No activity yet" />
        @else
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($recentActivity as $entry)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground"><i data-lucide="history" class="size-4"></i></span>
                        <div class="min-w-0 flex-1">
                            <span class="block truncate text-sm text-foreground">{{ $entry->description }}</span>
                            <span class="block text-[13px] text-muted-foreground">{{ $entry->user->name ?? 'System' }} · {{ class_basename($entry->loggable_type) }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-muted-foreground">{{ $entry->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
            @can('view-audit-logs')
                <x-slot:footer>
                    <span class="text-muted-foreground">Full history with filters</span>
                    <x-ui.button variant="outline" size="sm" :href="route('admin.activity.index')">Open activity log</x-ui.button>
                </x-slot:footer>
            @endcan
        @endif
    </x-ui.card>
@endsection
