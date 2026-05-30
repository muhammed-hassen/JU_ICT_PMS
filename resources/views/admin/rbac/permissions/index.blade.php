@extends('layouts.master')

@section('subtitle', 'Permissions')
@section('content_header_title', 'Permissions')
@section('content_header_subtitle', 'Manage the permission catalog')

@section('content_body')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Permission Catalog</h3>
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">Create Permission</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Roles</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr>
                                <td><code>{{ $permission->name }}</code></td>
                                <td>{{ config('rbac.modules')[$permission->module] ?? \Illuminate\Support\Str::headline($permission->module) }}</td>
                                <td>{{ $permission->description ?: 'No description' }}</td>
                                <td>{{ $permission->roles_count }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No permissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($permissions->hasPages())
            <div class="card-footer clearfix">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
@stop
