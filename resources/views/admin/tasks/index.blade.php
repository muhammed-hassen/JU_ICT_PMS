<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@extends('adminlte::page')

@section('title', 'Task Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1>
            <i class="fas fa-tasks text-primary"></i>
            Task Management
        </h1>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.tasks.my') }}" class="btn btn-info mr-2">
                <i class="fas fa-user-check"></i> My Tasks
            </a>
            <a href="{{ route('admin.tasks.kanban') }}" class="btn btn-primary mr-2">
                <i class="fas fa-columns"></i> Board View
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Tasks</h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $tasks->total() }} Total</span>
            </div>
        </div>
        
        {{-- Filters --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.tasks.index') }}" class="row">
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>
                                    {{ $priority->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group">
                        <label>Assignee</label>
                        <select name="assignee" class="form-control form-control-sm">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group">
                        <label>Overdue</label>
                        <select name="overdue" class="form-control form-control-sm">
                            <option value="">All Tasks</option>
                            <option value="1" {{ request('overdue') == 1 ? 'selected' : '' }}>Overdue Only</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="form-group d-flex" style="margin-top: 26px;">
                        <button type="submit" class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Priority Stats --}}
        @include('admin.tasks.partials.priority-stats')

        {{-- Task List --}}
        <div class="card-body p-0">
            @if($tasks->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No tasks found.</p>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Go to Projects
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 30px">#</th>
                                <th>Task</th>
                                <th>Project / Phase</th>
                                <th>Priority / Deadline</th>
                                <th>Status</th>
                                <th>Assignee</th>
                                <th>Progress</th>
                                <th style="min-width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>
                                            <a href="{{ route('admin.tasks.show', $task) }}">
                                                {{ Str::limit($task->title, 40) }}
                                            </a>
                                        </strong>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit($task->description, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $task->phase->project->name ?? 'N/A' }}</span>
                                        <br><small>{{ $task->phase->name ?? 'No Phase' }}</small>
                                    </td>
                                    <td>
                                        @include('admin.tasks.partials.priority-badge')
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $task->status_color }}">
                                            {{ $task->status->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->assignee)
                                            <span class="text-sm">{{ $task->assignee->name }}</span>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
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
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.tasks.show', $task) }}"
                                               class="btn btn-info"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('edit-task')
                                                <a href="{{ route('admin.tasks.edit', $task) }}"
                                                   class="btn btn-warning"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete-task')
                                                <form action="{{ route('admin.tasks.destroy', $task) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete">
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
    /* Priority & Deadline badges */
    .priority-badge, .deadline-badge {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
    .gap-1 {
        gap: 4px;
    }
    .progress-bar-striped {
        background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
    }
    
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

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterInputs = document.querySelectorAll('select[name="status"], select[name="priority"], select[name="assignee"], select[name="overdue"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
</script>
@endpush