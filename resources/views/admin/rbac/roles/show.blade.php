@extends('layouts.console')

@section('title', 'Role Details')

@php
    // The controller eager loads the relations but not the *_count columns, so count the loaded collections.
    $permissionCount = $role->permissions->count();
    $userCount = $role->users->count();
    $totalPermissions = \App\Models\Permission::count();
    $coverage = $totalPermissions > 0 ? round(($permissionCount / $totalPermissions) * 100) : 0;
@endphp

@section('content_header')
    @canvisit(route('admin.roles.index'))
        <a href="{{ route('admin.roles.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Roles
        </a>
    @endcanvisit
    <x-ui.page-header :title="$role->name" description="Role details, permissions and the users who hold it.">
        <x-ui.button variant="outline" onclick="window.print()">
            <i data-lucide="printer" class="size-4"></i>
            Print
        </x-ui.button>
        @if ($userCount === 0)
            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="m-0"
                  onsubmit="return confirm('Delete this role? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="destructive">
                    <i data-lucide="trash-2" class="size-4"></i>
                    Delete
                </x-ui.button>
            </form>
        @endif
        @canvisit(route('admin.roles.edit', $role))
            <x-ui.button :href="route('admin.roles.edit', $role)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit role
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="grid min-w-0 content-start gap-6 lg:col-span-2">
            {{-- Overview --}}
            <x-ui.card>
                <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Role ID</dt>
                        <dd class="tabular m-0 mt-1 font-medium">#{{ $role->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Guard</dt>
                        <dd class="m-0 mt-1.5"><x-ui.badge>{{ $role->guard_name }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Permissions</dt>
                        <dd class="tabular m-0 mt-1 font-medium">{{ $permissionCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Users</dt>
                        <dd class="tabular m-0 mt-1 font-medium">{{ $userCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Created</dt>
                        <dd class="m-0 mt-1 font-medium">
                            {{ $role->created_at ? $role->created_at->format('M d, Y h:i A') : 'N/A' }}
                            @if ($role->created_at)
                                <span class="block text-[13px] font-normal text-muted-foreground">{{ $role->created_at->diffForHumans() }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Last updated</dt>
                        <dd class="m-0 mt-1 font-medium">
                            {{ $role->updated_at ? $role->updated_at->format('M d, Y h:i A') : 'N/A' }}
                            @if ($role->updated_at)
                                <span class="block text-[13px] font-normal text-muted-foreground">{{ $role->updated_at->diffForHumans() }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <div class="mt-5 border-t border-border pt-4">
                    <p class="m-0 text-[13px] text-muted-foreground">Description</p>
                    <p class="m-0 mt-1 max-w-3xl">{{ $role->description ?: 'No description provided' }}</p>
                </div>
            </x-ui.card>

            {{-- Permissions --}}
            <x-ui.card :title="'Permissions (' . $permissionCount . ')'" description="What this role is allowed to do, grouped by module." flush>
                @if ($role->permissions->isNotEmpty())
                    <div class="grid items-start gap-4 p-5 md:grid-cols-2">
                        @foreach ($role->permissions->sortBy('name')->groupBy('module') as $module => $permissions)
                            <div class="min-w-0 overflow-hidden rounded-xl border border-border">
                                <div class="flex items-center justify-between gap-3 border-b border-border bg-muted px-4 py-2.5">
                                    <p class="m-0 text-sm font-semibold text-foreground">{{ config('rbac.modules')[$module] ?? ucfirst($module) }}</p>
                                    <x-ui.badge>{{ $permissions->count() }}</x-ui.badge>
                                </div>
                                <ul class="m-0 list-none divide-y divide-border p-0">
                                    @foreach ($permissions as $permission)
                                        <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                                            <div class="min-w-0">
                                                <p class="m-0 font-mono text-[13px] font-medium text-foreground">{{ $permission->name }}</p>
                                                <p class="m-0 text-xs text-muted-foreground">{{ $permission->description ?: 'No description' }}</p>
                                            </div>
                                            @canvisit(route('admin.permissions.show', $permission))
                                                <x-ui.icon-button icon="eye" :label="'View permission ' . $permission->name" :href="route('admin.permissions.show', $permission)" />
                                            @endcanvisit
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state icon="key-round" title="No permissions assigned" description="This role has no permissions yet.">
                        @canvisit(route('admin.roles.edit', $role))
                            <x-ui.button :href="route('admin.roles.edit', $role)">
                                <i data-lucide="plus" class="size-4"></i>
                                Assign permissions
                            </x-ui.button>
                        @endcanvisit
                    </x-ui.empty-state>
                @endif
            </x-ui.card>

            {{-- Users --}}
            <x-ui.card :title="'Users (' . $userCount . ')'" description="People who currently hold this role." flush>
                @if ($role->users->isNotEmpty())
                    <ul class="m-0 list-none divide-y divide-border p-0">
                        @foreach ($role->users as $user)
                            <li class="flex items-center gap-3 px-5 py-3">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                                    {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('users.show', $user) }}" class="font-medium text-foreground no-underline hover:text-primary hover:no-underline">{{ $user->name }}</a>
                                    <p class="m-0 truncate text-[13px] text-muted-foreground">{{ $user->email }}</p>
                                </div>
                                @canvisit(route('users.show', $user))
                                    <x-ui.icon-button icon="eye" :label="'View ' . $user->name" :href="route('users.show', $user)" />
                                @endcanvisit
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state icon="users" title="No users assigned" description="This role is not assigned to any users yet." />
                @endif
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="grid min-w-0 content-start gap-6">
            <x-ui.card title="Permission coverage">
                <x-ui.progress :value="$coverage" />
                <p class="m-0 mt-2 text-[13px] text-muted-foreground">{{ $permissionCount }} of {{ $totalPermissions }} permissions</p>
            </x-ui.card>

            @if ($userCount > 0)
                <x-ui.card title="Delete role">
                    <p class="m-0 text-sm text-muted-foreground">
                        This role is held by {{ $userCount }} {{ \Illuminate\Support\Str::plural('user', $userCount) }}. Remove it from all users before deleting it.
                    </p>
                </x-ui.card>
            @endif
        </div>
    </div>
@endsection
