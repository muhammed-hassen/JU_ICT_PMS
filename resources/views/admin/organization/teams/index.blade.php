@extends('layouts.master')

@section('subtitle', 'Teams')
@section('content_header_title', 'Teams')
@section('content_header_subtitle', 'Organization structure')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Team Catalog</h3>
            @can('create-team')
                <a href="{{ route('admin.organization.teams.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Team
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Leader</th>
                            <th>Members</th>
                            <th>Child Teams</th>
                            <th>Projects</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teams as $team)
                            <tr>
                                <td>{{ $team->name }}</td>
                                <td>{{ $team->parentTeam?->name ?: 'Top level' }}</td>
                                <td>{{ $team->teamLeader?->name ?: 'Unassigned' }}</td>
                                <td>{{ $team->members_count }}</td>
                                <td>{{ $team->child_teams_count }}</td>
                                <td>{{ $team->projects_count }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.organization.teams.show', $team) }}" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                    @canany(['edit-team', 'assign-team-leader', 'manage-team-members'])
                                        <a href="{{ route('admin.organization.teams.edit', $team) }}" class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>
                                    @endcanany
                                    @can('delete-team')
                                        <form action="{{ route('admin.organization.teams.destroy', $team) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No teams found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($teams->hasPages())
            <div class="card-footer clearfix">
                {{ $teams->links() }}
            </div>
        @endif
    </div>
@stop
