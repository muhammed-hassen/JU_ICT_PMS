@extends('layouts.master')

@section('subtitle', 'Project Details')
@section('content_header_title', 'Project Details')
@section('content_header_subtitle', $project->name)

@section('content_body')
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit Project
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Project Name:</strong>
                            <p>{{ $project->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'archived' => 'dark'
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Template:</strong>
                            <p>{{ $project->template?->name ?? 'No template used' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Created By:</strong>
                            <p>{{ $project->creator?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Start Date:</strong>
                            <p>{{ $project->start_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>End Date:</strong>
                            <p>{{ $project->end_date?->format('M d, Y') ?? 'Not set' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p>{{ $project->description ?: 'No description' }}</p>
                        </div>
                        <div class="col-12">
                            <strong>Overall Progress:</strong>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: {{ $project->progress }}%;" 
                                     aria-valuenow="{{ $project->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $project->progress }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assigned Teams & Members</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Teams:</strong>
                            @if($project->teams->count() > 0)
                                <ul>
                                    @foreach($project->teams as $team)
                                        <li>{{ $team->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No teams assigned</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Individual Members:</strong>
                            @if($project->members->count() > 0)
                                <ul>
                                    @foreach($project->members as $member)
                                        <li>{{ $member->name }} ({{ $member->email }})</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No individual members assigned</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Stats</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <h3>{{ $project->phases->count() }}</h3>
                            <small>Total Phases</small>
                        </div>
                        <div class="col-6 text-center">
                            <h3>{{ $project->phases->sum(fn($p) => $p->tasks->count()) }}</h3>
                            <small>Total Tasks</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small>Created at: {{ $project->created_at->format('M d, Y H:i') }}</small><br>
                        <small>Last updated: {{ $project->updated_at->format('M d, Y H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phases and Tasks Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Project Phases & Tasks</h3>
        </div>
        <div class="card-body">
            @if($project->phases->count() > 0)
                <div class="accordion" id="phasesAccordion">
                    @foreach($project->phases as $phaseIndex => $phase)
                        <div class="card">
                            <div class="card-header" id="heading{{ $phase->id }}">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" 
                                            data-toggle="collapse" 
                                            data-target="#collapse{{ $phase->id }}" 
                                            aria-expanded="{{ $phaseIndex === 0 ? 'true' : 'false' }}" 
                                            aria-controls="collapse{{ $phase->id }}">
                                        <strong>Phase {{ $phaseIndex + 1 }}: {{ $phase->name }}</strong>
                                        <span class="float-right">
                                            <span class="badge badge-info">{{ $phase->tasks->count() }} tasks</span>
                                            <span class="badge badge-success">{{ $phase->progress_percentage }}% complete</span>
                                        </span>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse{{ $phase->id }}" 
                                 class="collapse {{ $phaseIndex === 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $phase->id }}" 
                                 data-parent="#phasesAccordion">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Task</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Est. Hours</th>
                                                    <th>Progress</th>
                                                    <th>Assigned To</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($phase->tasks as $task)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $task->title }}</strong>
                                                            @if($task->description)
                                                                <br><small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $priorityColors = [
                                                                    'Low' => 'secondary',
                                                                    'Medium' => 'info',
                                                                    'High' => 'warning',
                                                                    'Critical' => 'danger'
                                                                ];
                                                            @endphp
                                                            <span class="badge badge-{{ $priorityColors[$task->priority->name ?? 'Low'] ?? 'secondary' }}">
                                                                {{ $task->priority->name ?? 'None' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusColors = [
                                                                    'Not Started' => 'secondary',
                                                                    'In Progress' => 'primary',
                                                                    'Under Review' => 'info',
                                                                    'Completed' => 'success',
                                                                    'Blocked' => 'danger',
                                                                    'Cancelled' => 'dark'
                                                                ];
                                                            @endphp
                                                            <span class="badge badge-{{ $statusColors[$task->status->name ?? 'Not Started'] ?? 'secondary' }}">
                                                                {{ $task->status->name ?? 'Not Started' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $task->estimated_hours ?? '-' }}</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: {{ $task->progress_percentage }}%;">
                                                                    {{ $task->progress_percentage }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $task->assignedTo?->name ?? 'Unassigned' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No phases created yet.
                    @if($project->template)
                        <br>This project was created from a template. Phases should appear here.
                    @endif
                </div>
            @endif
        </div>
    </div>
@stop