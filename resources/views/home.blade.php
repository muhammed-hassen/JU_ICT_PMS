{{-- resources/views/home.blade.php --}}
@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">
                <i class="fas fa-chart-pie text-primary"></i>
                Dashboard
            </h1>
            <small class="text-muted">Welcome back, {{ auth()->user()->name }}!</small>
        </div>
        <div>
            <span class="badge badge-success p-2">
                <i class="fas fa-check-circle"></i> System Online
            </span>
            <span class="badge badge-info p-2 ml-2">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('F d, Y') }}
            </span>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats Row with Icons and Gradient Colors --}}
    <div class="row">
        {{-- Projects Card --}}
        @can('view-projects')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-info">
                    <div class="inner">
                        <h3>{{ $projectsCount ?? 0 }}</h3>
                        <p>Total Projects</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-project-diagram fa-fw"></i>
                    </div>
                    <a href="{{ route('admin.projects.index') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- Tasks Card --}}
        @can('view-tasks')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h3>{{ $tasksCount ?? 0 }}</h3>
                        <p>Total Tasks</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks fa-fw"></i>
                    </div>
                    <a href="{{ route('admin.tasks.index') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- Users Card --}}
        @can('view-all-users')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-warning">
                    <div class="inner">
                        <h3>{{ $usersCount ?? 0 }}</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users fa-fw"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- Overdue Tasks Card --}}
        @can('view-tasks')
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-danger">
                    <div class="inner">
                        <h3>{{ $overdueTasks ?? 0 }}</h3>
                        <p>Overdue Tasks</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle fa-fw"></i>
                    </div>
                    <a href="{{ route('admin.tasks.index', ['overdue' => 1]) }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan
    </div>

    {{-- Charts Row --}}
    <div class="row">
        {{-- Task Status Chart --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Task Status Distribution
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Project Status Chart --}}
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Project Status
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="projectStatusChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity & Projects Row --}}
    <div class="row">
        {{-- Recent Projects --}}
        @can('view-projects')
            <div class="col-md-8">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i> Recent Projects
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-primary">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(isset($recentProjects) && $recentProjects->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentProjects as $project)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.projects.show', $project) }}" class="font-weight-bold">
                                                        {{ $project->name }}
                                                    </a>
                                                    @if($project->description)
                                                        <br><small class="text-muted">{{ Str::limit($project->description, 40) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'secondary') }}">
                                                        {{ ucfirst($project->status ?? 'Draft') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px; width: 100px;">
                                                        <div class="progress-bar bg-{{ ($project->progress_percentage ?? 0) >= 100 ? 'success' : 'primary' }}"
                                                             role="progressbar"
                                                             style="width: {{ $project->progress_percentage ?? 0 }}%">
                                                            {{ $project->progress_percentage ?? 0 }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt"></i> {{ $project->created_at->diffForHumans() }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> {{ $project->creator->name ?? 'N/A' }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No projects created yet.</p>
                                @can('create-project')
                                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Your First Project
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endcan

        {{-- Quick Stats / Team Summary --}}
        <div class="col-md-4">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-crown"></i> Quick Summary
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="fas fa-tasks text-primary"></i> Total Tasks
                        </span>
                        <span class="font-weight-bold">{{ $tasksCount ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="fas fa-check-circle text-success"></i> Completed
                        </span>
                        <span class="font-weight-bold text-success">{{ $completedTasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="fas fa-spinner text-warning"></i> In Progress
                        </span>
                        <span class="font-weight-bold text-warning">{{ $inProgressTasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="fas fa-clock text-info"></i> Not Started
                        </span>
                        <span class="font-weight-bold text-info">{{ $notStartedTasks ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">
                            <i class="fas fa-exclamation-circle text-danger"></i> Overdue
                        </span>
                        <span class="font-weight-bold text-danger">{{ $overdueTasks ?? 0 }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">
                            <i class="fas fa-users"></i> Team Members
                        </span>
                        <span class="font-weight-bold">{{ $teamMembersCount ?? 0 }}</span>
                    </div>
                    @can('view-organization-structure')
                        <a href="{{ route('admin.organization.teams.index') }}" class="btn btn-info btn-block mt-3">
                            <i class="fas fa-users-cog"></i> Manage Teams
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }
    .small-box .inner h3 {
        font-size: 2.2rem;
        font-weight: 700;
    }
    .small-box .inner p {
        font-weight: 500;
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    .badge {
        padding: 8px 12px;
        border-radius: 20px;
    }
    .table th {
        border-top: none;
    }
    .progress {
        border-radius: 10px;
        background-color: #f0f0f0;
    }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #0d6efd); }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745, #20c997); }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545, #e83e8c); }
    .bg-gradient-primary { background: linear-gradient(135deg, #0d6efd, #6610f2); }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== Task Status Chart =====
        var ctx1 = document.getElementById('taskStatusChart').getContext('2d');
        var taskStatusChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started', 'Overdue'],
                datasets: [{
                    data: [
                        {{ $completedTasks ?? 0 }},
                        {{ $inProgressTasks ?? 0 }},
                        {{ $notStartedTasks ?? 0 }},
                        {{ $overdueTasks ?? 0 }}
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // ===== Project Status Chart =====
        var ctx2 = document.getElementById('projectStatusChart').getContext('2d');
        var projectStatusChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($statusLabels ?? ['Draft', 'Active', 'Completed', 'Archived']) !!},
                datasets: [{
                    label: 'Projects by Status',
                    data: {!! json_encode($statusData ?? [0, 0, 0, 0]) !!},
                    backgroundColor: ['#6c757d', '#17a2b8', '#28a745', '#ffc107'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    borderRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endpush