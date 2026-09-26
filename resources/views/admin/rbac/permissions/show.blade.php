@extends('layouts.console')

@section('title', 'Permission Details')

@php
    // The controller eager loads roles but not roles_count, so count the loaded collection.
    $roleCount = $permission->roles->count();
    $totalRoles = \App\Models\Role::count();
    $coverage = $totalRoles > 0 ? round(($roleCount / $totalRoles) * 100) : 0;
    $permission->roles->loadCount(['users', 'permissions']);
@endphp

@section('content_header')
    @canvisit(route('admin.permissions.index'))
        <a href="{{ route('admin.permissions.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Permissions
        </a>
    @endcanvisit
    <x-ui.page-header :title="$permission->name" description="Permission details and the roles that grant it.">
        <x-ui.button variant="outline" onclick="window.print()">
            <i data-lucide="printer" class="size-4"></i>
            Print
        </x-ui.button>
        @if ($roleCount === 0)
            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="m-0"
                  onsubmit="return confirm('Delete this permission? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="destructive">
                    <i data-lucide="trash-2" class="size-4"></i>
                    Delete
                </x-ui.button>
            </form>
        @endif
        @canvisit(route('admin.permissions.edit', $permission))
            <x-ui.button :href="route('admin.permissions.edit', $permission)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit permission
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
                        <dt class="text-[13px] font-normal text-muted-foreground">Permission ID</dt>
                        <dd class="tabular m-0 mt-1 font-medium">#{{ $permission->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Module</dt>
                        <dd class="m-0 mt-1.5"><x-ui.badge>{{ ucfirst($permission->module) }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Guard</dt>
                        <dd class="m-0 mt-1.5"><x-ui.badge>{{ $permission->guard_name }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Assigned roles</dt>
                        <dd class="tabular m-0 mt-1 font-medium">{{ $roleCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Created</dt>
                        <dd class="m-0 mt-1 font-medium">
                            {{ $permission->created_at ? $permission->created_at->format('M d, Y h:i A') : 'N/A' }}
                            @if ($permission->created_at)
                                <span class="block text-[13px] font-normal text-muted-foreground">{{ $permission->created_at->diffForHumans() }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[13px] font-normal text-muted-foreground">Last updated</dt>
                        <dd class="m-0 mt-1 font-medium">
                            {{ $permission->updated_at ? $permission->updated_at->format('M d, Y h:i A') : 'N/A' }}
                            @if ($permission->updated_at)
                                <span class="block text-[13px] font-normal text-muted-foreground">{{ $permission->updated_at->diffForHumans() }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                <div class="mt-5 border-t border-border pt-4">
                    <p class="m-0 text-[13px] text-muted-foreground">Description</p>
                    <p class="m-0 mt-1 max-w-3xl">{{ $permission->description ?: 'No description provided' }}</p>
                </div>
            </x-ui.card>

            {{-- Roles --}}
            <x-ui.card :title="'Assigned roles (' . $roleCount . ')'" description="Every role that grants this permission." flush>
                @if ($permission->roles->isNotEmpty())
                    <ul class="m-0 list-none divide-y divide-border p-0">
                        @foreach ($permission->roles as $role)
                            <li class="flex items-center gap-3 px-5 py-3">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-ju-navy">
                                    <i data-lucide="shield" class="size-4"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.roles.show', $role) }}" class="font-medium text-foreground no-underline hover:text-primary hover:no-underline">{{ $role->name }}</a>
                                    <p class="tabular m-0 text-[13px] text-muted-foreground">
                                        {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }},
                                        {{ $role->permissions_count }} {{ \Illuminate\Support\Str::plural('permission', $role->permissions_count) }}
                                    </p>
                                </div>
                                @canvisit(route('admin.roles.show', $role))
                                    <x-ui.icon-button icon="eye" :label="'View role ' . $role->name" :href="route('admin.roles.show', $role)" />
                                @endcanvisit
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state icon="shield" title="No roles assigned" description="This permission is not assigned to any role yet.">
                        @canvisit(route('admin.roles.index'))
                            <x-ui.button variant="outline" :href="route('admin.roles.index')">
                                Manage roles
                            </x-ui.button>
                        @endcanvisit
                    </x-ui.empty-state>
                @endif
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="grid min-w-0 content-start gap-6">
            <x-ui.card title="Role coverage">
                <x-ui.progress :value="$coverage" />
                <p class="m-0 mt-2 text-[13px] text-muted-foreground">{{ $roleCount }} of {{ $totalRoles }} roles</p>
            </x-ui.card>

            @if ($roleCount > 0)
                <x-ui.card title="Delete permission">
                    <p class="m-0 text-sm text-muted-foreground">
                        This permission is granted by {{ $roleCount }} {{ \Illuminate\Support\Str::plural('role', $roleCount) }}. Remove it from all roles before deleting it.
                    </p>
                </x-ui.card>
            @endif
        </div>
    </div>
@endsection
