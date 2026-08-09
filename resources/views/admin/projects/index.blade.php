@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Projects')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-project-diagram text-primary"></i>
            Projects
            <small>Manage all projects</small>
        </h1>
        @can('create-project')
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Project
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Project List</h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $projects->total() }} Total</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($projects->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No projects found.</p>
                    @can('create-project')
                        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Project
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Project Name</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Phases</th>
                                <th>Teams</th>
                                <th>Progress</th>
                                <th>Created By</th>
                                <th style="min-width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>{{ $project->id }}</td>
                                    <td>
                                        <strong>
                                            <a href="{{ route('admin.projects.show', $project) }}">
                                                {{ $project->name }}
                                            </a>
                                        </strong>
                                        @if($project->description)
                                            <br><small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->template)
                                            <span class="badge bg-info">{{ $project->template->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">No template</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'active' => 'success',
                                                'completed' => 'primary',
                                                'archived' => 'dark'
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $project->phases_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $project->teams->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 100px;">
                                            <div class="progress-bar bg-{{ ($project->progress_percentage ?? 0) >= 100 ? 'success' : 'primary' }}"
                                                 role="progressbar"
                                                 style="width: {{ $project->progress_percentage ?? 0 }}%">
                                                {{ $project->progress_percentage ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $project->creator?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- VIEW - All authenticated users --}}
                                            <a href="{{ route('admin.projects.show', $project) }}" 
                                               class="btn btn-info" 
                                               title="View Project">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            {{-- EDIT - Only users with edit-project permission --}}
                                            @can('edit-project')
                                                <a href="{{ route('admin.projects.edit', $project) }}" 
                                                   class="btn btn-warning" 
                                                   title="Edit Project">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            
                                            {{-- DELETE - Only users with delete-project permission --}}
                                            @can('delete-project')
                                                <form action="{{ route('admin.projects.destroy', $project) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Delete this project? All phases and tasks will be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete Project">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if(isset($projects) && method_exists($projects, 'hasPages') && $projects->hasPages())
            <div class="card-footer clearfix">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    .table th {
        border-top: none;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush