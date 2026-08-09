<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">
<div class="row">
    {{-- Overall Progress --}}
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Project Progress
                </h3>
                <div class="card-tools">
                    <span class="badge bg-primary">{{ number_format($progressStats['overall_progress'], 1) }}% Complete</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="progress-group">
                            <span class="progress-text">Overall Progress</span>
                            <span class="progress-number"><b>{{ number_format($progressStats['overall_progress'], 1) }}%</b></span>
                            <div class="progress">
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ $progressStats['overall_progress'] }}%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress-group mt-3">
                            <span class="progress-text">Phases</span>
                            <span class="progress-number">
                                <b>{{ $progressStats['completed_phases'] }}</b>/{{ $progressStats['total_phases'] }}
                            </span>
                            <div class="progress">
                                @php
                                    $phaseProgress = $progressStats['total_phases'] > 0 
                                        ? ($progressStats['completed_phases'] / $progressStats['total_phases']) * 100 
                                        : 0;
                                @endphp
                                <div class="progress-bar bg-success" style="width: {{ $phaseProgress }}%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress-group mt-3">
                            <span class="progress-text">Tasks</span>
                            <span class="progress-number">
                                <b>{{ $progressStats['completed_tasks'] }}</b>/{{ $progressStats['total_tasks'] }}
                            </span>
                            <div class="progress">
                                @php
                                    $taskProgress = $progressStats['total_tasks'] > 0 
                                        ? ($progressStats['completed_tasks'] / $progressStats['total_tasks']) * 100 
                                        : 0;
                                @endphp
                                <div class="progress-bar bg-info" style="width: {{ $taskProgress }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="badge badge-success">Completed Phases</span>
                                <h3>{{ $progressStats['completed_phases'] }}</h3>
                            </div>
                            <div>
                                <span class="badge badge-warning">Active Phases</span>
                                <h3>{{ $progressStats['active_phases'] }}</h3>
                            </div>
                            <div>
                                <span class="badge badge-secondary">Not Started</span>
                                <h3>{{ $progressStats['not_started_phases'] }}</h3>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <span class="badge badge-success">Completed Tasks</span>
                                <h3>{{ $progressStats['completed_tasks'] }}</h3>
                            </div>
                            <div>
                                <span class="badge badge-warning">In Progress</span>
                                <h3>{{ $progressStats['in_progress_tasks'] }}</h3>
                            </div>
                            <div>
                                <span class="badge badge-secondary">Not Started</span>
                                <h3>{{ $progressStats['not_started_tasks'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>