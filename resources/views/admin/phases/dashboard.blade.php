@extends('layouts.master')

@section('subtitle', 'Phase Management')
@section('content_header_title', '📊 Phase Management')
@section('content_header_subtitle', 'Overview of all project phases and milestones')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('content_header_actions')
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Project
    </a>
    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-list"></i> All Projects
    </a>
@endsection

@section('content_body')
<div class="container-fluid fade-in">
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ \App\Models\Project::count() }}</h3>
                    <p>Total Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <a href="{{ route('admin.projects.index') }}" class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View All Projects
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3>{{ \App\Models\Phase::count() }}</h3>
                    <p>Total Phases</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <a href="#" class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View All Phases
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Task::count() }}</h3>
                    <p>Total Tasks</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <a href="#" class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View All Tasks
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger">
                <div class="inner">
                    <h3>{{ \App\Models\Phase::whereHas('status', function($q) { $q->where('name', 'Completed'); })->count() }}</h3>
                    <p>Completed Phases</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View Completed
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="fw-bold text-muted me-3">Quick Actions:</span>
                        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Project
                        </a>
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-copy"></i> Manage Templates
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> All Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects with Phases Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list me-2 text-primary"></i>
                Projects with Phases
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $projects->total() }} Projects</span>
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Project</th>
                                <th class="text-center">Phases</th>
                                <th class="text-center">Tasks</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $index => $project)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $projects->firstItem() + $index }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="project-icon me-3" style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #4facfe, #1f75cb); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                                                {{ strtoupper(substr($project->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong class="text-dark">{{ $project->name }}</strong>
                                                @if($project->description)
                                                    <br><small class="text-muted">{{ Str::limit($project->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info badge-pill" style="font-size: 14px; padding: 6px 14px;">
                                            <i class="fas fa-layer-group me-1"></i>
                                            {{ $project->phases_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary badge-pill" style="font-size: 14px; padding: 6px 14px;">
                                            <i class="fas fa-tasks me-1"></i>
                                            {{ $project->phases->sum(fn($p) => $p->tasks->count()) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress" style="height: 8px; width: 120px;">
                                                <div class="progress-bar bg-{{ $project->progress_percentage >= 80 ? 'success' : ($project->progress_percentage >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $project->progress_percentage ?? 0 }}%;">
                                                </div>
                                            </div>
                                            <span class="ms-2 text-muted small fw-bold">
                                                {{ number_format($project->progress_percentage ?? 0, 0) }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'active' => 'success',
                                                'completed' => 'primary',
                                                'archived' => 'dark'
                                            ];
                                            $statusIcons = [
                                                'draft' => 'fa-pen',
                                                'active' => 'fa-play',
                                                'completed' => 'fa-check-circle',
                                                'archived' => 'fa-archive'
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$project->status] ?? 'secondary' }} badge-pill" style="padding: 6px 14px;">
                                            <i class="fas {{ $statusIcons[$project->status] ?? 'fa-circle' }} me-1"></i>
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.projects.show', $project) }}" 
                                               class="btn btn-outline-info" 
                                               title="View Project"
                                               data-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.projects.phases.index', $project) }}" 
                                               class="btn btn-outline-success" 
                                               title="Manage Phases"
                                               data-toggle="tooltip">
                                                <i class="fas fa-layer-group"></i>
                                            </a>
                                            <a href="{{ route('admin.projects.phases.create', $project) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Add Phase"
                                               data-toggle="tooltip">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="{{ route('admin.projects.edit', $project) }}" 
                                               class="btn btn-outline-warning" 
                                               title="Edit Project"
                                               data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Projects Found</h5>
                    <p class="text-muted mb-3">Create your first project to start managing phases.</p>
                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Project
                    </a>
                </div>
            @endif
        </div>
        @if($projects->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects
                    </span>
                    {{ $projects->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Stats Cards Bottom -->
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3">
                                <i class="fas fa-rocket fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ \App\Models\Project::where('status', 'active')->count() }}</h5>
                            <p class="text-muted mb-0">Active Projects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-success bg-opacity-10 rounded-3">
                                <i class="fas fa-check-double fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ \App\Models\Project::where('status', 'completed')->count() }}</h5>
                            <p class="text-muted mb-0">Completed Projects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-warning bg-opacity-10 rounded-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ \App\Models\Project::where('status', 'draft')->count() }}</h5>
                            <p class="text-muted mb-0">Draft Projects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #0d6efd 100%) !important;
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%) !important;
    }
    .bg-opacity-10 {
        opacity: 0.1;
    }
    .project-icon {
        flex-shrink: 0;
    }
    .gap-3 {
        gap: 1rem;
    }
    .fw-bold {
        font-weight: 700 !important;
    }
    .text-dark {
        color: #1a2332 !important;
    }
</style>
@endpush