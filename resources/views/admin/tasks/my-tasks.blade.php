<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
@extends('adminlte::page')
@section('title', 'My Tasks')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1>
            <i class="fas fa-user-check text-info"></i>
            My Tasks
        </h1>
        <div class="mt-2 mt-md-0">
            <span class="badge bg-primary mr-2">Total: {{ $stats['total'] }}</span>
            <span class="badge bg-success mr-2">Completed: {{ $stats['completed'] }}</span>
            <span class="badge bg-warning mr-2">In Progress: {{ $stats['in_progress'] }}</span>
            <span class="badge bg-danger mr-2">Overdue: {{ $stats['overdue'] }}</span>
        </div>
    </div>
@endsection

@section('content')
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['not_started'] }}</h3>
                    <p>Not Started</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['in_progress'] }}</h3>
                    <p>In Progress</p>
                </div>
                <div class="icon">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['completed'] }}</h3>
                    <p>Completed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['overdue'] }}</h3>
                    <p>Overdue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Your Assigned Tasks</h3>
            <div class="card-tools">
                <form method="GET" class="form-inline flex-wrap">
                    <select name="status" class="form-control form-control-sm mr-2 mb-1">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="priority" class="form-control form-control-sm mr-2 mb-1">
                        <option value="">All Priorities</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>
                                {{ $priority->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm mr-2 mb-1" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary btn-sm mb-1">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.tasks.my') }}" class="btn btn-secondary btn-sm ml-1 mb-1">
                        <i class="fas fa-times"></i>
                    </a>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            @if($tasks->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No tasks assigned to you!</p>
                    <p class="text-muted">Enjoy your free time 🎉</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Deadline</th>
                                <th style="min-width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($task->title, 40) }}</strong>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit($task->description, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $task->phase->project->name ?? 'N/A' }}
                                        </span>
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
                                        <div class="progress" style="height: 20px; width: 70px;">
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
                                                    <br><small>{{ $task->days_overdue }} days overdue</small>
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    {{ $task->deadline->format('M d, Y') }}
                                                    @if($task->days_remaining)
                                                        <br><small>{{ $task->days_remaining }} days left</small>
                                                    @endif
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">No deadline</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.tasks.show', $task) }}"
                                               class="btn btn-info"
                                               title="View Task">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @can('edit-own-task')
                                                <a href="{{ route('admin.tasks.edit', $task) }}"
                                                   class="btn btn-warning"
                                                   title="Edit Task">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            
                                            @if(auth()->user()->can('complete-task') || auth()->user()->can('edit-task'))
                                                <form action="{{ route('admin.tasks.update-status', $task) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="task_status_id" 
                                                            class="form-control form-control-sm d-inline" 
                                                            style="width: auto; display: inline-block; height: 32px;"
                                                            onchange="this.closest('form').submit()">
                                                        @foreach($statuses as $status)
                                                            <option value="{{ $status->id }}"
                                                                    {{ $task->task_status_id == $status->id ? 'selected' : '' }}>
                                                                {{ $status->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        {{-- Pagination - FIXED --}}
        @if($tasks->hasPages())
            <div class="card-footer clearfix">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-md-0">
                        Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} tasks
                    </div>
                    <div class="pagination-wrapper">
                        {{ $tasks->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    /* ===== PAGINATION RESPONSIVE FIX ===== */
    .pagination-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
        padding: 2px 0;
    }
    
    .pagination-wrapper .pagination {
        flex-wrap: nowrap;
        margin-bottom: 0;
    }
    
    .pagination-wrapper .pagination .page-item .page-link {
        padding: 0.35rem 0.6rem;
        font-size: 0.8rem;
    }
    
    /* Mobile responsive for pagination */
    @media (max-width: 768px) {
        .pagination-wrapper .pagination .page-item .page-link {
            padding: 0.25rem 0.4rem;
            font-size: 0.7rem;
        }
        .pagination-wrapper .pagination .page-item .page-link span {
            display: none;
        }
        .pagination-wrapper .pagination .page-item .page-link i {
            display: inline-block !important;
        }
    }
    
    @media (max-width: 576px) {
        .pagination-wrapper .pagination .page-item .page-link {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }
        .pagination-wrapper .pagination .page-item .page-link .d-none.d-sm-block {
            display: none !important;
        }
    }
</style>
@endpush