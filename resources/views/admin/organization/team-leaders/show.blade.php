@extends('layouts.console')

@section('title', 'Team Leader Details')

@section('content_header')
    @canvisit(route('admin.organization.team-leaders.index'))
        <a href="{{ route('admin.organization.team-leaders.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Back
        </a>
    @endcanvisit
    <x-ui.page-header :title="$teamLeader->name" description="Team leader">
        @canvisit(route('admin.organization.team-leaders.edit', $teamLeader))
            <x-ui.button variant="outline" :href="route('admin.organization.team-leaders.edit', $teamLeader)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit Team Leader
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    <x-ui.card title="Team Leader Profile">
        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Email</dt>
                <dd class="m-0 mt-1 font-medium">{{ $teamLeader->email }}</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Teams Led</dt>
                <dd class="m-0 mt-1.5 flex flex-wrap gap-1.5">
                    @forelse ($teamLeader->ledTeams as $team)
                        @if (auth()->user()->isDirector() || in_array($team->id, auth()->user()->getTeamIds()))
                            <a href="{{ route('admin.organization.teams.show', $team) }}" class="no-underline hover:no-underline">
                                <x-ui.badge variant="primary" class="hover:bg-primary/20">{{ $team->name }} ({{ $team->members->count() }} members)</x-ui.badge>
                            </a>
                        @else
                            <x-ui.badge variant="primary">{{ $team->name }} ({{ $team->members->count() }} members)</x-ui.badge>
                        @endif
                    @empty
                        <span class="text-sm text-muted-foreground">No teams assigned.</span>
                    @endforelse
                </dd>
            </div>
        </dl>
    </x-ui.card>
@endsection
