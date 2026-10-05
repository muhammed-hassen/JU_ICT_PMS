@extends('layouts.console')

@section('title', 'Edit Team Leader')

@section('content_header')
    @canvisit(route('admin.organization.team-leaders.show', $teamLeader))
        <a href="{{ route('admin.organization.team-leaders.show', $teamLeader) }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
            <i data-lucide="arrow-left" class="size-4"></i>
            {{ $teamLeader->name }}
        </a>
    @endcanvisit
    <x-ui.page-header title="Edit Team Leader" :description="$teamLeader->name" />
@endsection

@section('content')
    <form action="{{ route('admin.organization.team-leaders.update', $teamLeader) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.organization.team-leaders._form', ['submitLabel' => 'Update Team Leader'])
    </form>
@endsection
