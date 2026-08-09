@extends('layouts.master')

@section('subtitle', 'Team Leader Details')
@section('content_header_title', $teamLeader->name)
@section('content_header_subtitle', 'Team leader')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Team Leader Profile</h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> {{ $teamLeader->email }}</p>
            <p><strong>Teams Led:</strong></p>
            @forelse ($teamLeader->ledTeams as $team)
                <a href="{{ route('admin.organization.teams.show', $team) }}" class="badge badge-info mr-1">
                    {{ $team->name }} ({{ $team->members->count() }} members)
                </a>
            @empty
                <span class="text-muted">No teams assigned.</span>
            @endforelse
        </div>
        <div class="card-footer">
            @canany(['assign-team-leader', 'manage-team-members'])
                <a href="{{ route('admin.organization.team-leaders.edit', $teamLeader) }}" class="btn btn-primary btn-sm">Edit Team Leader</a>
            @endcanany
            <a href="{{ route('admin.organization.team-leaders.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>
@stop
