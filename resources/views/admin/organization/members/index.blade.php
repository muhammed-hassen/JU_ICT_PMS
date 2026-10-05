@extends('layouts.console')

@section('title', 'Members')

@section('content_header')
    <x-ui.page-header title="Members" description="People in your teams and the teams they belong to.">
        @canvisit(route('admin.organization.members.create'))
            <x-ui.button :href="route('admin.organization.members.create')">
                <i data-lucide="user-plus" class="size-4"></i>
                Create Member
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@stop

@section('content')

    <x-ui.card title="Member Directory" flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Email</x-ui.th>
                        <x-ui.th>Teams</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                            <x-ui.td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                                        {{ \Illuminate\Support\Str::of($member->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                    </span>
                                    <span class="font-medium">{{ $member->name }}</span>
                                </div>
                            </x-ui.td>
                            <x-ui.td class="text-muted-foreground">{{ $member->email }}</x-ui.td>
                            <x-ui.td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($member->teams as $team)
                                        <x-ui.badge variant="primary">{{ $team->name }}</x-ui.badge>
                                    @empty
                                        <span class="text-muted-foreground">Unassigned</span>
                                    @endforelse
                                </div>
                            </x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5">
                                    @canvisit(route('admin.organization.members.show', $member))
                                        <x-ui.icon-button icon="eye" label="View member" :href="route('admin.organization.members.show', $member)" />
                                    @endcanvisit
                                    @canvisit(route('admin.organization.members.edit', $member))
                                        <x-ui.icon-button icon="pencil" label="Edit member" :href="route('admin.organization.members.edit', $member)" />
                                    @endcanvisit
                                    @canvisit(route('admin.organization.members.destroy', $member), 'DELETE')
                                        <form action="{{ route('admin.organization.members.destroy', $member) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete member" tone="destructive" />
                                        </form>
                                    @endcanvisit
                                </div>
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state icon="users" title="No members found." description="Members of your teams will show up here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $members->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@stop
