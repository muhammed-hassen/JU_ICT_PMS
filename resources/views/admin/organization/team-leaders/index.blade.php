@extends('layouts.master')

@section('subtitle', 'Team Leaders')
@section('content_header_title', 'Team Leaders')
@section('content_header_subtitle', 'Team leadership')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Team Leader Directory</h3>
            @can('assign-team-leader')
                <a href="{{ route('admin.organization.team-leaders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Team Leader
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Leads</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teamLeaders as $teamLeader)
                            <tr>
                                <td>{{ $teamLeader->name }}</td>
                                <td>{{ $teamLeader->email }}</td>
                                <td>
                                    @forelse ($teamLeader->ledTeams as $team)
                                        <span class="badge badge-info">{{ $team->name }}</span>
                                    @empty
                                        <span class="text-muted">No teams assigned</span>
                                    @endforelse
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.organization.team-leaders.show', $teamLeader) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @canany(['assign-team-leader', 'manage-team-members'])
                                        <a href="{{ route('admin.organization.team-leaders.edit', $teamLeader) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcanany
                                    @can('assign-team-leader')
                                        <form action="{{ route('admin.organization.team-leaders.destroy', $teamLeader) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team leader?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No team leaders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($teamLeaders->hasPages())
            <div class="card-footer clearfix">
                {{ $teamLeaders->links() }}
            </div>
        @endif
    </div>
@stop
