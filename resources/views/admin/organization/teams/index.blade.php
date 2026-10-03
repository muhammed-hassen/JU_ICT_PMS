@extends('layouts.console')

@section('title', 'Teams')

@section('content_header')
    <x-ui.page-header title="Teams" description="How the organization is split into teams.">
        @canvisit(route('admin.organization.teams.create'))
            <x-ui.button :href="route('admin.organization.teams.create')">
                <i data-lucide="plus" class="size-4"></i>
                Create Team
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@stop

@section('content')

    <x-ui.card title="Team Catalog" flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        <x-ui.th>Name</x-ui.th>
                        <x-ui.th>Parent</x-ui.th>
                        <x-ui.th>Leader</x-ui.th>
                        <x-ui.th class="text-right">Members</x-ui.th>
                        <x-ui.th class="text-right">Child Teams</x-ui.th>
                        <x-ui.th class="text-right">Projects</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teams as $team)
                        <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                            <x-ui.td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-ju-navy">
                                        <i data-lucide="users" class="size-4"></i>
                                    </span>
                                    <span class="font-medium">{{ $team->name }}</span>
                                </div>
                            </x-ui.td>
                            <x-ui.td class="text-muted-foreground">{{ $team->parentTeam?->name ?: 'Top level' }}</x-ui.td>
                            <x-ui.td :class="$team->teamLeader ? '' : 'text-muted-foreground'">{{ $team->teamLeader?->name ?: 'Unassigned' }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $team->members_count }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $team->child_teams_count }}</x-ui.td>
                            <x-ui.td class="tabular text-right">{{ $team->projects_count }}</x-ui.td>
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5">
                                    @canvisit(route('admin.organization.teams.show', $team))
                                        <x-ui.icon-button icon="eye" label="View team" :href="route('admin.organization.teams.show', $team)" />
                                    @endcanvisit
                                    @canvisit(route('admin.organization.teams.edit', $team))
                                        <x-ui.icon-button icon="pencil" label="Edit team" :href="route('admin.organization.teams.edit', $team)" />
                                    @endcanvisit
                                    @canvisit(route('admin.organization.teams.destroy', $team), 'DELETE')
                                        <form action="{{ route('admin.organization.teams.destroy', $team) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this team?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete team" tone="destructive" />
                                        </form>
                                    @endcanvisit
                                </div>
                            </x-ui.td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="network" title="No teams found." description="Teams you belong to will show up here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($teams->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $teams->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@stop
