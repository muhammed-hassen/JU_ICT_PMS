@extends('layouts.master')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('subtitle', 'Create Task')
@section('content_header_title', '➕ Create Task')
@section('content_header_subtitle', 'Add a new task to ' . $phase->name)

@section('content_header_actions')
    <a href="{{ route('admin.phases.show', $phase) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Phase
    </a>
@endsection

@section('content_body')
<div class="row">
    <div class="col-md-8">
        <div class="card fade-in">
            <form action="{{ route('admin.tasks.store', $phase) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Task Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" 
                               placeholder="e.g., Design database schema" 
                               required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="Describe the task in detail">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

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
                                            {{ old('task_status_id') == $status->id ? 'selected' : '' }}>
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
                                    <option value="">None</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" 
                                            {{ old('task_priority_id') == $priority->id ? 'selected' : '' }}>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_to">Assign To</label>
                                <select name="assigned_to" 
                                        id="assigned_to" 
                                        class="form-control select2 @error('assigned_to') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="date" 
                                       name="due_date" 
                                       id="due_date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estimated_hours">Estimated Hours</label>
                                <input type="number" 
                                       step="0.5" 
                                       min="0" 
                                       name="estimated_hours" 
                                       id="estimated_hours" 
                                       class="form-control @error('estimated_hours') is-invalid @enderror" 
                                       value="{{ old('estimated_hours') }}" 
                                       placeholder="0.0">
                                @error('estimated_hours')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="progress_percentage">Initial Progress (%)</label>
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="progress_percentage" 
                                       id="progress_percentage" 
                                       class="form-control @error('progress_percentage') is-invalid @enderror" 
                                       value="{{ old('progress_percentage', 0) }}">
                                @error('progress_percentage')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" 
                               name="sort_order" 
                               id="sort_order" 
                               class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', $maxOrder) }}" 
                               min="1">
                        <small class="text-muted">Leave empty to add at the end</small>
                        @error('sort_order')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Task
                    </button>
                    <a href="{{ route('admin.phases.show', $phase) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phase Information</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-5">Phase Name</dt>
                    <dd class="col-sm-7">{{ $phase->name }}</dd>

                    <dt class="col-sm-5">Project</dt>
                    <dd class="col-sm-7">
                        <a href="{{ route('admin.projects.show', $phase->project) }}">
                            {{ $phase->project->name }}
                        </a>
                    </dd>

                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        <span class="badge badge-{{ $phase->statusColor }}">
                            {{ $phase->status?->name ?? 'Not Started' }}
                        </span>
                    </dd>

                    <dt class="col-sm-5">Progress</dt>
                    <dd class="col-sm-7">
                        {{ number_format($phase->progress_percentage ?? 0, 1) }}%
                    </dd>

                    <dt class="col-sm-5">Tasks</dt>
                    <dd class="col-sm-7">{{ $phase->tasks->count() }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Tips</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="ms-2">Tasks can be assigned to team members</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="ms-2">Set priorities to organize work</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="ms-2">Track progress with percentage</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="ms-2">Due dates help with deadlines</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop