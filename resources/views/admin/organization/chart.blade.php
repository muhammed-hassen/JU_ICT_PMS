@extends('layouts.console')
@section('title', 'Organization chart')

@section('content_header')
    <x-ui.page-header title="Organization chart" description="ICT Director, then teams and sub-teams, each with its leader and members. It updates as teams and roles change." />
@endsection

@section('content')
    <x-ui.card title="ICT Director">
        @forelse ($directors as $director)
            <p class="m-0 inline-flex items-center gap-2 font-medium"><i data-lucide="briefcase-business" class="size-4 text-muted-foreground"></i>{{ $director->name }}</p>
        @empty
            <p class="m-0 text-muted-foreground">No director assigned.</p>
        @endforelse
    </x-ui.card>

    <div class="mt-4 grid gap-4 border-l-2 border-border pl-4 sm:pl-6">
        @forelse ($teams as $team)
            @include('admin.organization.partials.chart-team', ['team' => $team, 'depth' => 0])
        @empty
            <x-ui.empty-state icon="users" title="No teams yet" description="Create teams to build the organization chart." />
        @endforelse
    </div>
@endsection
