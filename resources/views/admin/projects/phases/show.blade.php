@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', $phase->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-layer-group text-primary"></i>
                {{ $phase->name }}
                <small>Phase Details</small>
            </h1>
        </div>
        <div>
            @can('edit-phase')
                <a href="{{ route('admin.phases.edit', $phase) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.projects.phases.index', $phase->project) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Phases
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- Phase Info Card --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <dl>
                                <dt>Project</dt>
                                <dd>
                                    <a href="{{ route('admin.projects.show', $phase->project) }}">
                                        {{ $phase->project->name }}
                                    </a>
                                </dd>
                                <dt>Status</dt>
                                <dd>
                                    <span class="badge badge-{{ $phase->status_color }}">
                                        {{ $phase->status?->name ?? 'N/A' }}
                                    </span>
                                </dd>
                                <dt>Sort Order</dt>
                                <dd>{{ $phase->sort_order }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Progress</dt>
                                <dd>
                                    <div class="progress" style="height: 25px; width: 80%;">
                                        <div class="progress-bar bg-{{ $phase->progress_percentage == 100 ? 'success' : 'primary' }}"
                                             role="progressbar"
                                             style="width: {{ $phase->progress_percentage }}%">
                                            {{ $phase->progress_percentage }}%
                                        </div>
                                    </div>
                                </dd>
                                <dt>Tasks</dt>
                                <dd>{{ $phase->tasks->count() }} tasks</dd>
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Start Date</dt>
                                <dd>{{ $phase->start_date ? $phase->start_date->format('M d, Y') : 'N/A' }}</dd>
                                <dt>End Date</dt>
                                <dd>{{ $phase->end_date ? $phase->end_date->format('M d, Y') : 'N/A' }}</dd>
                                @if($phase->start_date && $phase->end_date)
                                    <dt>Planned Duration</dt>
                                    <dd>{{ $phase->planned_duration }} days</dd>
                                @endif
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Created</dt>
                                <dd>{{ $phase->created_at->diffForHumans() }}</dd>
                                <dt>Created By</dt>
                                <dd>{{ $phase->creator->name ?? 'Unknown' }}</dd>
                                <dt>Last Updated</dt>
                                <dd>{{ $phase->updated_at->diffForHumans() }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if($phase->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <dt>Description</dt>
                                <dd>{{ $phase->description }}</dd>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Task Stats --}}
        <div class="col-md-12">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $phase->task_stats['total'] ?? 0 }}</h3>
                            <p>Total Tasks</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $phase->task_stats['completed'] ?? 0 }}</h3>
                            <p>Completed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $phase->task_stats['in_progress'] ?? 0 }}</h3>
                            <p>In Progress</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $phase->task_stats['blocked'] ?? 0 }}</h3>
                            <p>Blocked</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tasks Section --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i>
                        Tasks in this Phase
                    </h3>
                    @can('create-task')
                        <div class="card-tools">
                            <a href="{{ route('admin.phases.tasks.create', $phase) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Add Task
                            </a>
                        </div>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if($phase->tasks->isEmpty())
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tasks in this phase yet.</p>
                            @can('create-task')
                                <a href="{{ route('admin.phases.tasks.create', $phase) }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Create First Task
                                </a>
                            @endcan
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assignee</th>
                                        <th>Progress</th>
                                        <th>Deadline</th>
                                        <th style="min-width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($phase->tasks as $task)
                                        <tr>
                                            <td>
                                                <strong>
                                                    <a href="{{ route('admin.tasks.show', $task) }}">
                                                        {{ $task->title }}
                                                    </a>
                                                </strong>
                                                @if($task->description)
                                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $task->priority_color }}">
                                                    {{ $task->priority->name ?? 'None' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $task->status_color }}">
                                                    {{ $task->status->name ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($task->assignee)
                                                    {{ $task->assignee->name }}
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px; width: 80px;">
                                                    <div class="progress-bar bg-{{ $task->progress_percentage == 100 ? 'success' : 'primary' }}"
                                                         role="progressbar"
                                                         style="width: {{ $task->progress_percentage }}%">
                                                        {{ $task->progress_percentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($task->deadline)
                                                    @if($task->isOverdue())
                                                        <span class="text-danger">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            {{ $task->deadline->format('M d, Y') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">{{ $task->deadline->format('M d, Y') }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No deadline</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- VIEW - All authenticated users --}}
                                                    <a href="{{ route('admin.tasks.show', $task) }}"
                                                       class="btn btn-info"
                                                       title="View Task">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    {{-- EDIT - Only users with edit-task permission --}}
                                                    @can('edit-task')
                                                        <a href="{{ route('admin.tasks.edit', $task) }}"
                                                           class="btn btn-warning"
                                                           title="Edit Task">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    
                                                    {{-- DELETE - Only users with delete-task permission --}}
                                                    @can('delete-task')
                                                        <form action="{{ route('admin.tasks.destroy', $task) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" title="Delete Task">
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
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .small-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-body .table td {
        vertical-align: middle;
    }
</style>
@endpush