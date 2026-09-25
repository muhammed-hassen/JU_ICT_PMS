@extends('layouts.master')

@section('subtitle', 'Phase Details')
@section('content_header_title', 'Phase Details')
@section('content_header_subtitle', $phase->name)
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('content_body')
    @if(session('status'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('status') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Phase Information -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Phase Information
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.phases.edit', $phase) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit Phase
                        </a>
                        <a href="{{ route('admin.projects.show', $phase->project) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Project
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Phase Name:</strong>
                            <p class="text-lg">{{ $phase->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p>
                                <span class="badge badge-{{ $phase->statusColor }} badge-pill p-2 px-3" style="font-size: 14px;">
                                    <i class="fas fa-{{ $phase->status?->name == 'Completed' ? 'check-circle' : ($phase->status?->name == 'In Progress' ? 'spinner fa-spin' : 'circle') }} mr-1"></i>
                                    {{ $phase->status?->name ?? 'Not Started' }}
                                </span>
                                @if($phase->isOverdue)
                                    <span class="badge badge-danger badge-pill p-2 px-3" style="font-size: 14px;">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Overdue
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Project:</strong>
                            <p>
                                <a href="{{ route('admin.projects.show', $phase->project) }}" class="text-primary">
                                    <i class="fas fa-project-diagram mr-1"></i>
                                    {{ $phase->project->name }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Sort Order:</strong>
                            <p><span class="badge badge-secondary">#{{ $phase->sort_order }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Start Date:</strong>
                            <p>{{ $phase->start_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>End Date:</strong>
                            <p>{{ $phase->end_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p class="text-muted">{{ $phase->description ?: 'No description provided.' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Progress:</strong>
                            <div class="progress" style="height: 30px; border-radius: 15px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated 
                                    bg-{{ $phase->progress_percentage >= 80 ? 'success' : ($phase->progress_percentage >= 50 ? 'warning' : 'danger') }}" 
                                    role="progressbar" 
                                    style="width: {{ $phase->progress_percentage ?? 0 }}%; border-radius: 15px;" 
                                    aria-valuenow="{{ $phase->progress_percentage ?? 0 }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ number_format($phase->progress_percentage ?? 0, 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-user mr-1"></i>
                                Created by: {{ $phase->creator?->name ?? 'Unknown' }}
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $phase->created_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-info" onclick="window.print();">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <form action="{{ route('admin.phases.duplicate', $phase) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Duplicate this phase?')">
                                    <i class="fas fa-copy"></i> Duplicate
                                </button>
                            </form>
                            <form action="{{ route('admin.phases.destroy', $phase) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this phase and all its tasks?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Section -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks text-success mr-2"></i>
                        Tasks ({{ $phase->tasks->count() }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tasks.create', $phase) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Add Task
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($phase->tasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Task Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Assigned To</th>
                                        <th>Progress</th>
                                        <th>Due Date</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($phase->tasks as $index => $task)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $task->title }}</strong>
                                                @if($task->description)
                                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $task->statusColor }} badge-pill">
                                                    {{ $task->status?->name ?? 'Not Started' }}
                                                </span>
                                                @if($task->isOverdue)
                                                    <span class="badge badge-danger badge-pill">
                                                        <i class="fas fa-clock"></i> Overdue
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $task->priorityColor ?? 'secondary' }} badge-pill">
                                                    {{ $task->priority?->name ?? 'None' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($task->assignedTo)
                                                    <span class="badge badge-info badge-pill">
                                                        <i class="fas fa-user mr-1"></i>
                                                        {{ $task->assignedTo->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px; width: 100px;">
                                                    <div class="progress-bar bg-{{ $task->progress_percentage >= 80 ? 'success' : ($task->progress_percentage >= 50 ? 'warning' : 'danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $task->progress_percentage ?? 0 }}%;">
                                                        {{ number_format($task->progress_percentage ?? 0, 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $task->due_date?->format('M d, Y') ?? '-' }}
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this task?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tasks in this phase.</p>
                            <a href="{{ route('admin.tasks.create', $phase) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add First Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>
                        Quick Stats
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h2 class="text-primary">{{ $phase->tasks->count() }}</h2>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                        <div class="col-6">
                            <h2 class="text-success">{{ $phase->tasks->where('status.name', 'Completed')->count() }}</h2>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-danger">{{ $phase->tasks->where('isOverdue', true)->count() }}</h3>
                            <small class="text-muted">Overdue</small>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning">{{ $phase->tasks->whereNull('assigned_to')->count() }}</h3>
                            <small class="text-muted">Unassigned</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock text-info mr-2"></i>
                        Timeline
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-inverse">
                        <!-- Start Date -->
                        <div class="time-label">
                            <span class="bg-success">Start</span>
                        </div>
                        <div>
                            <i class="fas fa-play bg-success"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $phase->start_date?->format('M d, Y') ?? 'Not set' }}
                                </span>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="time-label">
                            <span class="bg-danger">End</span>
                        </div>
                        <div>
                            <i class="fas fa-flag bg-danger"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $phase->end_date?->format('M d, Y') ?? 'Not set' }}
                                    @if($phase->isOverdue)
                                        <span class="badge badge-danger">Overdue</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="time-label">
                            <span class="bg-info">Progress</span>
                        </div>
                        <div>
                            <i class="fas fa-chart-line bg-info"></i>
                            <div class="timeline-item">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $phase->progress_percentage >= 80 ? 'success' : ($phase->progress_percentage >= 50 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $phase->progress_percentage ?? 0 }}%;">
                                        {{ number_format($phase->progress_percentage ?? 0, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Created -->
                        <div>
                            <i class="fas fa-user bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $phase->created_at->diffForHumans() }}
                                </span>
                                <h6 class="timeline-header">
                                    Created by {{ $phase->creator?->name ?? 'Unknown' }}
                                </h6>
                            </div>
                        </div>

                        <!-- Last Updated -->
                        @if($phase->updated_at != $phase->created_at)
                            <div>
                                <i class="fas fa-edit bg-secondary"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $phase->updated_at->diffForHumans() }}
                                    </span>
                                    <h6 class="timeline-header">Last updated</h6>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    .timeline-inverse .time-label span {
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
    }
    .timeline-item {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        margin-left: 10px;
    }
</style>
@endpush