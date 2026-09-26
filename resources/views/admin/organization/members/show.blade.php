@extends('layouts.console')

@section('title', 'Member Details')

@section('content_header')
    @canvisit(route('admin.organization.members.index'))
        <a href="{{ route('admin.organization.members.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Members
        </a>
    @endcanvisit
    <x-ui.page-header :title="$member->name" description="Team member">
        @canvisit(route('admin.organization.members.edit', $member))
            <x-ui.button variant="outline" :href="route('admin.organization.members.edit', $member)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit Member
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@stop

@section('content')

    <x-ui.card title="Member Profile" class="max-w-3xl">
        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Email</dt>
                <dd class="m-0 mt-1 font-medium">{{ $member->email }}</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Teams</dt>
                <dd class="m-0 mt-1.5 flex flex-wrap gap-1">
                    @forelse ($member->teams as $team)
                        <a href="{{ route('admin.organization.teams.show', $team) }}" class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-[11px] font-semibold leading-4 text-ju-blue-700 no-underline hover:bg-primary/20 hover:no-underline">{{ $team->name }}</a>
                    @empty
                        <span class="text-muted-foreground">No teams assigned.</span>
                    @endforelse
                </dd>
            </div>
        </dl>
    </x-ui.card>
@stop
