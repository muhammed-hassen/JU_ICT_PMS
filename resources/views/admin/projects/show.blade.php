{{-- resources/views/admin/projects/show.blade.php --}}
@extends('adminlte::page')

@section('title', $project->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fas fa-project-diagram text-primary"></i>
                {{ $project->name }}
                <small>Project Details</small>
            </h1>
        </div>
        <div>
            @can('edit-project')
                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    {{-- Project Info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <dl>
                                <dt>Project Name</dt>
                                <dd>{{ $project->name }}</dd>
                                <dt>Status</dt>
                                <dd>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'active' => 'success',
                                            'completed' => 'primary',
                                            'archived' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                        {{ ucfirst($project->status ?? 'Draft') }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Progress</dt>
                                <dd>
                                    <div class="progress" style="height: 25px; width: 80%;">
                                        <div class="progress-bar bg-{{ ($project->progress_percentage ?? 0) >= 100 ? 'success' : 'primary' }}"
                                             role="progressbar"
                                             style="width: {{ $project->progress_percentage ?? 0 }}%">
                                            {{ $project->progress_percentage ?? 0 }}%
                                        </div>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Start Date</dt>
                                <dd>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</dd>
                                <dt>End Date</dt>
                                <dd>{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-3">
                            <dl>
                                <dt>Template</dt>
                                <dd>{{ $project->template->name ?? 'Custom' }}</dd>
                                <dt>Created By</dt>
                                <dd>{{ $project->creator->name ?? 'Unknown' }}</dd>
                            </dl>
                        </div>
                    </div>
                    @if($project->description)
                        <div class="row mt-2">
                            <div class="col-12">
                                <dt>Description</dt>
                                <dd>{{ $project->description }}</dd>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Stats --}}
    @include('admin.projects.partials.progress-stats')

    {{-- Progress Charts --}}
    @include('admin.projects.partials.progress-charts')

    {{-- Timeline --}}
    @include('admin.projects.partials.timeline')

    {{-- Phases Section --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i>
                        Project Phases ({{ $project->phases->count() }})
                    </h3>
                    @can('create-phase')
                        <div class="card-tools">
                            <a href="{{ route('admin.projects.phases.create', $project) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Phase
                            </a>
                        </div>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if($project->phases->isEmpty())
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No phases in this project yet.</p>
                            @can('create-phase')
                                <a href="{{ route('admin.projects.phases.create', $project) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create First Phase
                                </a>
                            @endcan
                        </div>
                    @else
                        <div class="row p-3">
                            @foreach($project->phases as $phase)
                                <div class="col-md-4">
                                    <div class="card card-{{ $phase->status_color }}">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <a href="{{ route('admin.phases.show', $phase) }}">
                                                    {{ $phase->name }}
                                                </a>
                                            </h5>
                                            <div class="card-tools">
                                                <span class="badge badge-{{ $phase->status_color }}">
                                                    {{ $phase->status?->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted">{{ Str::limit($phase->description ?? '', 80) }}</p>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $phase->progress_percentage >= 100 ? 'success' : 'primary' }}"
                                                     role="progressbar"
                                                     style="width: {{ $phase->progress_percentage }}%">
                                                    {{ $phase->progress_percentage }}%
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-tasks"></i> {{ $phase->tasks->count() }} tasks
                                                    @if($phase->end_date)
                                                        | <i class="fas fa-calendar"></i> {{ $phase->end_date->format('M d, Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <a href="{{ route('admin.phases.show', $phase) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @can('edit-phase')
                                                <a href="{{ route('admin.phases.edit', $phase) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            @endcan
                                            @can('delete-phase')
                                                <form action="{{ route('admin.phases.destroy', $phase) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Delete this phase? All tasks will be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
.card-body .card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.card-body .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card-header .card-title a {
    color: inherit;
    text-decoration: none;
}
.card-header .card-title a:hover {
    text-decoration: underline;
}
.progress-group {
    margin-bottom: 10px;
}
.progress-group .progress-text {
    font-weight: 600;
}
.progress-group .progress-number {
    float: right;
}
</style>
@endpush

@stack('js')