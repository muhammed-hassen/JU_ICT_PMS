@extends('layouts.master')

@section('subtitle', 'Projects')
@section('content_header_title', 'Projects')
@section('content_header_subtitle', 'Manage all projects')

@section('content_body')
    @if(session('status'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Project List</h3>
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Project
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>
                                    <strong>{{ $project->name }}</strong>
                                    @if($project->description)
                                        <br><small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($project->template)
                                        <span class="badge badge-info">{{ $project->template->name }}</span>
                                    @else
                                        <span class="badge badge-secondary">No template</span>
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
                                <td>{{ $project->phases_count }}</td>
                                <td>{{ $project->teams->count() }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $project->progress }}%;" 
                                             aria-valuenow="{{ $project->progress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $project->progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $project->creator?->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.projects.destroy', $project) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Delete this project? All phases and tasks will be deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No projects found. <a href="{{ route('admin.projects.create') }}">Create your first project</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($projects->hasPages())
            <div class="card-footer">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@stop