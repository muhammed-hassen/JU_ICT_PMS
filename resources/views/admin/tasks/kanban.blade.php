@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Task Board')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-columns text-primary"></i>
            Task Board
            <small>Kanban View</small>
        </h1>
        <div>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> List View
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Task
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.tasks.kanban') }}" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Project</label>
                        <select name="project" class="form-control">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Assignee</label>
                        <select name="assignee" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>
                                    {{ $priority->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.tasks.kanban') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Priority Stats --}}
    @php
        $tasks = collect();
        foreach ($boardData as $status => $data) {
            $tasks = $tasks->merge($data['tasks']);
        }
    @endphp
    @include('admin.tasks.partials.priority-stats', ['tasks' => $tasks])

    {{-- Status Stats --}}
    <div class="row mb-3">
        @foreach($statusStats as $status => $stats)
            <div class="col-md-2 col-6">
                <div class="small-box bg-{{ $stats['color'] }}">
                    <div class="inner">
                        <h3>{{ $stats['count'] }}</h3>
                        <p>{{ $status }} ({{ $stats['percentage'] }}%)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kanban Board --}}
    <div class="row" id="kanban-board">
        @foreach($boardData as $status => $data)
            <div class="col-md-2 col-6">
                <div class="card card-{{ $data['status']->color }}">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            {{ $status }}
                            <span class="badge badge-light ml-2">{{ $data['count'] }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-2 kanban-column" 
                         data-status="{{ $status }}"
                         style="min-height: 300px; max-height: 600px; overflow-y: auto;">
                        
                        @forelse($data['tasks'] as $task)
                            <div class="kanban-item card card-sm mb-2" 
                                 data-task-id="{{ $task->id }}"
                                 draggable="true">
                                <div class="card-body p-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <a href="{{ route('admin.tasks.show', $task) }}" 
                                               class="font-weight-bold text-dark">
                                                {{ Str::limit($task->title, 25) }}
                                            </a>
                                        </div>
                                        <div class="mt-1">
                                            @include('admin.tasks.partials.priority-badge')
                                        </div>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-user"></i> 
                                            {{ $task->assignee->name ?? 'Unassigned' }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-project-diagram"></i> 
                                            {{ $task->phase->project->name ?? 'N/A' }}
                                        </small>
                                        @if($task->progress_percentage > 0)
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-{{ $task->progress_percentage == 100 ? 'success' : 'primary' }}"
                                                     style="width: {{ $task->progress_percentage }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted p-3">
                                <small>No tasks</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('css')
<style>
    .kanban-column {
        background: #f8f9fa;
        border-radius: 5px;
        min-height: 300px;
        padding: 8px;
    }
    .kanban-item {
        cursor: grab;
        border-left: 4px solid #007bff;
        transition: all 0.2s;
        border-radius: 4px;
    }
    .kanban-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .kanban-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    .kanban-column.drag-over {
        background: #e9ecef;
        border: 2px dashed #007bff;
    }
    .small-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-sm .card-body {
        padding: 0.5rem;
    }
    .gap-1 {
        gap: 4px;
    }
    .priority-badge, .deadline-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // DRAG AND DROP
    // ============================================================
    const columns = document.querySelectorAll('.kanban-column');
    const items = document.querySelectorAll('.kanban-item');

    items.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.taskId);
            this.classList.add('dragging');
        });
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        column.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const taskId = e.dataTransfer.getData('text/plain');
            const newStatus = this.dataset.status;
            
            fetch('{{ route("admin.tasks.kanban-reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to move task. ' + (data.error || ''));
                }
            })
            .catch(error => {
                alert('Error moving task');
            });
        });
    });
});
</script>
@endpush