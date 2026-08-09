@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Edit Task')

@section('content_header')
    <h1>
        <i class="fas fa-edit text-warning"></i>
        Edit Task: {{ $task->title }}
    </h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.tasks.update', $task) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- Title --}}
                        <div class="form-group">
                            <label for="title">Task Title <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="title"
                                   id="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="Enter task title"
                                   value="{{ old('title', $task->title) }}"
                                   required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description"
                                      id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Describe the task in detail">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status and Priority --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task_status_id">Status <span class="text-danger">*</span></label>
                                    <select name="task_status_id"
                                            id="task_status_id"
                                            class="form-control @error('task_status_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                    {{ old('task_status_id', $task->task_status_id) == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('task_status_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="task_priority_id">Priority</label>
                                    <select name="task_priority_id"
                                            id="task_priority_id"
                                            class="form-control @error('task_priority_id') is-invalid @enderror">
                                        <option value="">Select Priority</option>
                                        @foreach($priorities as $priority)
                                            <option value="{{ $priority->id }}"
                                                    {{ old('task_priority_id', $task->task_priority_id) == $priority->id ? 'selected' : '' }}>
                                                {{ $priority->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('task_priority_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Priority Score --}}
                        <div class="form-group">
                            <label>Priority Score</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $task->calculatePriorityScore() }} / 100" 
                                       disabled>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <span class="badge badge-{{ $task->getPriorityBadgeColorAttribute() }}">
                                            {{ $task->getPriorityLevelAttribute() }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Auto-calculated based on priority level and deadline urgency
                            </small>
                        </div>

                        {{-- Deadline Status --}}
                        @if($task->deadline)
                            <div class="form-group">
                                <label>Deadline Status</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $task->getDeadlineBadgeAttribute() }}" 
                                           disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <span class="badge badge-{{ $task->getDeadlineColorAttribute() }}">
                                                @if($task->isOverdue())
                                                    <i class="fas fa-exclamation-triangle"></i> Overdue
                                                @elseif($task->getDeadlineStatusAttribute() == 'urgent')
                                                    <i class="fas fa-clock"></i> Urgent
                                                @else
                                                    <i class="fas fa-calendar-check"></i> On Track
                                                @endif
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Assignment --}}
                        @can('assign-task')
                            <div class="form-group">
                                <label for="assigned_to">Assign To</label>
                                <select name="assigned_to"
                                        id="assigned_to"
                                        class="form-control @error('assigned_to') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                                {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endcan

                        {{-- Progress --}}
                        <div class="form-group">
                            <label for="progress_percentage">Progress (%)</label>
                            <input type="number"
                                   name="progress_percentage"
                                   id="progress_percentage"
                                   class="form-control @error('progress_percentage') is-invalid @enderror"
                                   min="0"
                                   max="100"
                                   value="{{ old('progress_percentage', $task->progress_percentage) }}">
                            @error('progress_percentage')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">0-100% complete</small>
                        </div>

                        {{-- Dates and Hours --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date"
                                           name="start_date"
                                           id="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date', $task->start_date ? $task->start_date->format('Y-m-d') : '') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="deadline">Deadline</label>
                                    <input type="date"
                                           name="deadline"
                                           id="deadline"
                                           class="form-control @error('deadline') is-invalid @enderror"
                                           value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d') : '') }}">
                                    @error('deadline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estimated_hours">Estimated Hours</label>
                                    <input type="number"
                                           name="estimated_hours"
                                           id="estimated_hours"
                                           class="form-control @error('estimated_hours') is-invalid @enderror"
                                           placeholder="0.00"
                                           step="0.5"
                                           min="0"
                                           value="{{ old('estimated_hours', $task->estimated_hours) }}">
                                    @error('estimated_hours')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Task
                        </button>
                        <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Info</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Created</dt>
                        <dd class="col-sm-6">{{ $task->created_at->diffForHumans() }}</dd>

                        <dt class="col-sm-6">Created By</dt>
                        <dd class="col-sm-6">{{ $task->creator->name ?? 'Unknown' }}</dd>

                        <dt class="col-sm-6">Last Updated</dt>
                        <dd class="col-sm-6">{{ $task->updated_at->diffForHumans() }}</dd>

                        @if($task->completed_at)
                            <dt class="col-sm-6">Completed</dt>
                            <dd class="col-sm-6">{{ $task->completed_at->diffForHumans() }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Priority Legend --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Priority Legend</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span><span class="badge badge-danger">Critical</span></span>
                        <span>Score: 60-100</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge badge-warning">High</span></span>
                        <span>Score: 45-59</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge badge-info">Medium</span></span>
                        <span>Score: 25-44</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="badge badge-success">Low</span></span>
                        <span>Score: 0-24</span>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Score = Priority Weight + Deadline Bonus
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .badge {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
</style>
@endpush