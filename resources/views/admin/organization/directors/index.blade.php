@extends('layouts.master')

@section('subtitle', 'Directors')
@section('content_header_title', 'Directors')
@section('content_header_subtitle', 'ICT leadership')

@section('content_body')
    @include('admin.organization._alerts')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Director Directory</h3>
            @can('manage-organization-structure')
                <a href="{{ route('admin.organization.directors.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create Director
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Led Teams</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($directors as $director)
                        <tr>
                            <td>{{ $director->name }}</td>
                            <td>{{ $director->email }}</td>
                            <td>{{ $director->led_teams_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.organization.directors.show', $director) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                @can('manage-organization-structure')
                                    <a href="{{ route('admin.organization.directors.edit', $director) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.organization.directors.destroy', $director) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this director?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No directors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($directors->hasPages())
            <div class="card-footer clearfix">{{ $directors->links() }}</div>
        @endif
    </div>
@stop
