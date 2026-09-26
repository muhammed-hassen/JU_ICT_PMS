@extends('layouts.console')

@section('title', 'Create Team Leader')

@section('content_header')
    @canvisit(route('admin.organization.team-leaders.index'))
        <a href="{{ route('admin.organization.team-leaders.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            Team Leaders
        </a>
    @endcanvisit
    <x-ui.page-header title="Create Team Leader" description="Add a team leader and pick the teams they lead." />
@endsection

@section('content')
    <form action="{{ route('admin.organization.team-leaders.store') }}" method="POST">
        @csrf
        @include('admin.organization.team-leaders._form', ['submitLabel' => 'Create Team Leader'])
    </form>
@endsection
