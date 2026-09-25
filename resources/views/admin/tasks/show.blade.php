@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', $task->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-tasks text-primary"></i>
            {{ $task->title }}
            <small>Task Details</small>
        </h1>
        <div>
            @can('edit-task')
                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- Main Task Details --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Information</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $task->status_color }}">{{ $task->status->name ?? 'Unknown' }}</span>
                        <span class="badge badge-{{ $task->priority_color }}">{{ $task->priority->name ?? 'None' }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Title</dt>
                                <dd>{{ $task->title }}</dd>

                                <dt>Description</dt>
                                <dd>{{ $task->description ?? 'No description' }}</dd>

                                <dt>Project</dt>
                                <dd>
                                    <a href="{{ route('admin.projects.show', $task->phase->project_id) }}">
                                        {{ $task->phase->project->name }}
                                    </a>
                                </dd>

                                <dt>Phase</dt>
                                <dd>{{ $task->phase->name }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Assignee</dt>
                                <dd>
                                    @if($task->assignee)
                                        <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" 
                                             class="img-circle img-size-32 mr-1">
                                        {{ $task->assignee->name }}
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </dd>

                                <dt>Estimated Hours</dt>
                                <dd>{{ $task->estimated_hours ?? 'N/A' }}</dd>

                                <dt>Start Date</dt>
                                <dd>{{ $task->start_date ? $task->start_date->format('M d, Y') : 'N/A' }}</dd>

                                <dt>Deadline</dt>
                                <dd>
                                    @if($task->deadline)
                                        @if($task->isOverdue())
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $task->deadline->format('M d, Y') }}
                                            </span>
                                            <br>
                                            <small class="text-danger">{{ $task->days_overdue }} days overdue</small>
                                        @else
                                            <span class="text-success">
                                                {{ $task->deadline->format('M d, Y') }}
                                            </span>
                                            @if($task->days_remaining)
                                                <br>
                                                <small>{{ $task->days_remaining }} days remaining</small>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted">No deadline set</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <label>Progress</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $task->progress_percentage == 100 ? 'success' : 'primary' }}"
                                     role="progressbar"
                                     style="width: {{ $task->progress_percentage }}%"
                                     aria-valuenow="{{ $task->progress_percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    {{ $task->progress_percentage }}% Complete
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Status Update --}}
                    @can('edit-task')
                        <div class="row mt-3">
                            <div class="col-12">
                                <form action="{{ route('admin.tasks.update-status', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-group">
                                        <label>Update Status</label>
                                        <div class="input-group">
                                            <select name="task_status_id" class="form-control">
                                                @foreach(App\Models\TaskStatus::all() as $status)
                                                    <option value="{{ $status->id }}" 
                                                            {{ $task->task_status_id == $status->id ? 'selected' : '' }}>
                                                        {{ $status->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-sync"></i> Update
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        Created: {{ $task->created_at->diffForHumans() }} by {{ $task->creator->name ?? 'Unknown' }}
                        @if($task->updated_at != $task->created_at)
                            | Updated: {{ $task->updated_at->diffForHumans() }}
                        @endif
                    </small>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Assignment Actions --}}
            @can('assign-task')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Assignment</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tasks.assign', $task) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Assign To</label>
                                <select name="user_id" class="form-control">
                                    <option value="">Select User</option>
                                    @foreach($availableAssignees ?? [] as $user)
                                        <option value="{{ $user->id }}"
                                                {{ $task->assigned_to == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Assign Task
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

            {{-- Task Stats --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Stats</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Status</dt>
                        <dd class="col-sm-6">
                            <span class="badge badge-{{ $task->status_color }}">
                                {{ $task->status->name ?? 'Unknown' }}
                            </span>
                        </dd>

                        <dt class="col-sm-6">Priority</dt>
                        <dd class="col-sm-6">
                            <span class="badge badge-{{ $task->priority_color }}">
                                {{ $task->priority->name ?? 'None' }}
                            </span>
                        </dd>

                        <dt class="col-sm-6">Progress</dt>
                        <dd class="col-sm-6">{{ $task->progress_percentage }}%</dd>

                        <dt class="col-sm-6">Completed</dt>
                        <dd class="col-sm-6">
                            @if($task->completed_at)
                                {{ $task->completed_at->diffForHumans() }}
                            @else
                                <span class="text-muted">Not completed</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Phase Progress --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Phase Progress</h3>
                </div>
                <div class="card-body">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-info"
                             style="width: {{ $task->phase->progress_percentage }}%">
                            {{ $task->phase->progress_percentage }}%
                        </div>
                    </div>
                    <p class="mt-2 text-center">
                        {{ $task->phase->tasks->count() }} tasks in this phase
                    </p>
                    <a href="{{ route('admin.phases.show', $task->phase) }}" class="btn btn-info btn-block">
                        <i class="fas fa-eye"></i> View Phase
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection