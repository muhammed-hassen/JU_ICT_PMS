@extends('layouts.console')

@section('title', 'Team Details')

@section('content_header')
    @canvisit(route('admin.organization.teams.index'))
        <a href="{{ route('admin.organization.teams.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Teams
        </a>
    @endcanvisit
    <x-ui.page-header :title="$team->name" description="Team details">
        @canvisit(route('admin.organization.teams.edit', $team))
            <x-ui.button variant="outline" :href="route('admin.organization.teams.edit', $team)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit Team
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@stop

@section('content')

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <x-ui.card title="Profile">
            <dl class="m-0 flex flex-col gap-4">
                <div>
                    <dt class="text-[13px] font-normal text-muted-foreground">Leader</dt>
                    <dd class="m-0 mt-1 font-medium">{{ $team->teamLeader?->name ?: 'Unassigned' }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] font-normal text-muted-foreground">Parent</dt>
                    <dd class="m-0 mt-1 font-medium">{{ $team->parentTeam?->name ?: 'Top level' }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] font-normal text-muted-foreground">Description</dt>
                    <dd @class(['m-0 mt-1', 'text-muted-foreground' => ! $team->description])>{{ $team->description ?: 'No description' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="flex flex-col gap-6 lg:col-span-2">
            <x-ui.card :title="'Members (' . $team->members->count() . ')'" flush>
                @if ($team->members->isEmpty())
                    <x-ui.empty-state icon="users" title="No members assigned." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-border bg-muted">
                                <tr>
                                    <x-ui.th>Name</x-ui.th>
                                    <x-ui.th>Email</x-ui.th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($team->members as $member)
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Child Teams">
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($team->childTeams as $childTeam)
                        {{-- Non-directors may only open teams they belong to (TeamController@show). --}}
                        @if (auth()->user()->isDirector() || in_array($childTeam->id, auth()->user()->getTeamIds()))
                            <a href="{{ route('admin.organization.teams.show', $childTeam) }}" class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-[11px] font-semibold leading-4 text-ju-blue-700 no-underline hover:bg-primary/20 hover:no-underline">
                                {{ $childTeam->name }}
                            </a>
                        @else
                            <x-ui.badge>{{ $childTeam->name }}</x-ui.badge>
                        @endif
                    @empty
                        <span class="text-sm text-muted-foreground">No child teams.</span>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
@stop
