@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'All Phases')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-layer-group text-primary"></i>
            All Phases
            <small>Manage all project phases</small>
        </h1>
        <div>
            @can('create-phase')
                <a href="{{ route('admin.phases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Phase
                </a>
            @endcan
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Phases</h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $phases->total() }} Total</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($phases->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No phases found.</p>
                    @can('create-phase')
                        <a href="{{ route('admin.phases.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Phase
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px">#</th>
                                <th>Phase Name</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Tasks</th>
                                <th>Progress</th>
                                <th>Dates</th>
                                <th style="min-width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phases as $phase)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>
                                            <a href="{{ route('admin.phases.show', $phase) }}">
                                                {{ $phase->name }}
                                            </a>
                                        </strong>
                                        @if($phase->description)
                                            <br><small class="text-muted">{{ Str::limit($phase->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.projects.show', $phase->project) }}">
                                            <span class="badge bg-info">{{ $phase->project->name ?? 'N/A' }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $phase->status_color }}">
                                            {{ $phase->status?->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $phase->tasks->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 100px;">
                                            <div class="progress-bar bg-{{ $phase->progress_percentage == 100 ? 'success' : 'primary' }}"
                                                 role="progressbar"
                                                 style="width: {{ $phase->progress_percentage }}%">
                                                {{ $phase->progress_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($phase->start_date)
                                            <small>Start: {{ $phase->start_date->format('M d, Y') }}</small>
                                        @endif
                                        @if($phase->end_date)
                                            <br><small>End: {{ $phase->end_date->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.phases.show', $phase) }}"
                                               class="btn btn-info"
                                               title="View Phase">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('edit-phase')
                                                <a href="{{ route('admin.phases.edit', $phase) }}"
                                                   class="btn btn-warning"
                                                   title="Edit Phase">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete-phase')
                                                <form action="{{ route('admin.phases.destroy', $phase) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this phase?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete Phase">
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
        @if(method_exists($phases, 'hasPages') && $phases->hasPages())
            <div class="card-footer clearfix">
                {{ $phases->links() }}
            </div>
        @endif
    </div>
@endsection