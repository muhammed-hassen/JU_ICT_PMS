@extends('layouts.master')

@section('subtitle', 'Members')
@section('content_header_title', 'Members')
@section('content_header_subtitle', 'Team membership')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Member Directory</h3>
            @canany(['assign-team-member', 'manage-team-members'])
                <a href="{{ route('admin.organization.members.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Member
                </a>
            @endcanany
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Teams</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @forelse ($member->teams as $team)
                                        <span class="badge badge-info">{{ $team->name }}</span>
                                    @empty
                                        <span class="text-muted">Unassigned</span>
                                    @endforelse
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.organization.members.show', $member) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @canany(['manage-team-members', 'transfer-team-member'])
                                        <a href="{{ route('admin.organization.members.edit', $member) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endcanany
                                    @canany(['remove-team-member', 'manage-team-members'])
                                        <form action="{{ route('admin.organization.members.destroy', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcanany
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($members->hasPages())
            <div class="card-footer clearfix">
                {{ $members->links() }}
            </div>
        @endif
    </div>
@stop
