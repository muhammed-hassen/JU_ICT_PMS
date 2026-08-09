@extends('layouts.master')

@section('subtitle', 'Team Details')
@section('content_header_title', $team->name)
@section('content_header_subtitle', 'Team details')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Profile</h3>
                </div>
                <div class="card-body">
                    <p><strong>Leader:</strong> {{ $team->teamLeader?->name ?: 'Unassigned' }}</p>
                    <p><strong>Parent:</strong> {{ $team->parentTeam?->name ?: 'Top level' }}</p>
                    <p><strong>Description:</strong></p>
                    <p class="text-muted">{{ $team->description ?: 'No description' }}</p>
                </div>
                <div class="card-footer">
                    @canany(['edit-team', 'assign-team-leader', 'manage-team-members'])
                        <a href="{{ route('admin.organization.teams.edit', $team) }}" class="btn btn-primary btn-sm">Edit Team</a>
                    @endcanany
                    <a href="{{ route('admin.organization.teams.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Members</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($team->members as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No members assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Child Teams</h3>
                </div>
                <div class="card-body">
                    @forelse ($team->childTeams as $childTeam)
                        <a href="{{ route('admin.organization.teams.show', $childTeam) }}" class="badge badge-info mr-1">
                            {{ $childTeam->name }}
                        </a>
                    @empty
                        <span class="text-muted">No child teams.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop
