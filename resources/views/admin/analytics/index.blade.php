@extends('layouts.master')

@section('subtitle', 'Analytics Dashboard')
@section('content_header_title', 'Analytics Dashboard')
@section('content_header_subtitle', 'Real-time project performance metrics')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('content_body')
<div class="container-fluid">
    <!-- Stats Cards with Animation -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info animated-card" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(23, 162, 184, 0.3);">
                <div class="inner">
                    <h3 class="counter" data-target="{{ $totalProjects ?? 0 }}">0</h3>
                    <p>Total Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View all projects
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success animated-card" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);">
                <div class="inner">
                    <h3 class="counter" data-target="{{ $activeProjects ?? 0 }}">0</h3>
                    <p>Active Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View active
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning animated-card" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(255, 193, 7, 0.3);">
                <div class="inner">
                    <h3 class="counter" data-target="{{ $completedProjects ?? 0 }}">0</h3>
                    <p>Completed Projects</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> View completed
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-danger animated-card" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3);">
                <div class="inner">
                    <h3>{{ number_format($avgCompletionTime ?? 0, 1) }} <small style="font-size: 18px;">days</small></h3>
                    <p>Average Completion Time</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> Performance
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row with Glassmorphism -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary glass-card" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px 16px 0 0; border-bottom: 2px solid #e9ecef;">
                    <h3 class="card-title" style="font-weight: 600; color: #2c3e50;">
                        <i class="fas fa-chart-pie text-primary mr-2"></i>
                        Project Status Distribution
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">Real-time</span>
                    </div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <canvas id="projectStatusChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-primary glass-card" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px 16px 0 0; border-bottom: 2px solid #e9ecef;">
                    <h3 class="card-title" style="font-weight: 600; color: #2c3e50;">
                        <i class="fas fa-chart-line text-success mr-2"></i>
                        Monthly Project Creation Trend
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">Last 12 months</span>
                    </div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <canvas id="monthlyTrendChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress & Completion -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary glass-card" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px 16px 0 0; border-bottom: 2px solid #e9ecef;">
                    <h3 class="card-title" style="font-weight: 600; color: #2c3e50;">
                        <i class="fas fa-tasks text-warning mr-2"></i>
                        Project Progress Distribution
                    </h3>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <canvas id="progressDistributionChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-primary glass-card" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
                <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px 16px 0 0; border-bottom: 2px solid #e9ecef;">
                    <h3 class="card-title" style="font-weight: 600; color: #2c3e50;">
                        <i class="fas fa-percentage text-info mr-2"></i>
                        Overall Completion Rate
                    </h3>
                </div>
                <div class="card-body" style="padding: 30px;">
                    <div class="text-center">
                        <div class="completion-ring">
                            <svg width="180" height="180" viewBox="0 0 180 180">
                                <circle cx="90" cy="90" r="75" fill="none" stroke="#e9ecef" stroke-width="12"/>
                                <circle cx="90" cy="90" r="75" fill="none" stroke="#28a745" stroke-width="12"
                                        stroke-dasharray="{{ (($completionRate ?? 0) / 100) * 471 }} 471"
                                        stroke-linecap="round"
                                        transform="rotate(-90 90 90)"/>
                            </svg>
                            <div class="completion-text">
                                <h2 class="mb-0" style="font-weight: 700; color: #28a745;">
                                    {{ number_format($completionRate ?? 0, 1) }}%
                                </h2>
                                <small class="text-muted">Completion Rate</small>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-center gap-4">
                                <div>
                                    <span class="badge badge-success badge-pill p-2 px-3" style="font-size: 14px;">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $completedTasks ?? 0 }} Completed
                                    </span>
                                </div>
                                <div>
                                    <span class="badge badge-secondary badge-pill p-2 px-3" style="font-size: 14px;">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $totalTasks ?? 0 }} Total Tasks
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers with Modern Table -->
    <div class="card card-outline card-primary glass-card" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.08);">
        <div class="card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 16px 16px 0 0; border-bottom: 2px solid #e9ecef;">
            <h3 class="card-title" style="font-weight: 600; color: #2c3e50;">
                <i class="fas fa-trophy text-warning mr-2"></i>
                Top Performers
            </h3>
            <div class="card-tools">
                <span class="badge badge-warning">Leaderboard</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if(isset($topPerformers) && $topPerformers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 15px 20px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">#</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;">User</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;" class="text-center">Total Tasks</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;" class="text-center">Completed</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #495057; border-bottom: 2px solid #dee2e6;" class="text-center">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPerformers as $index => $user)
                                <tr style="transition: all 0.2s ease;">
                                    <td style="padding: 15px 20px; vertical-align: middle;">
                                        @if($index == 0)
                                            <span class="badge badge-warning" style="font-size: 16px; padding: 5px 12px; border-radius: 50%;">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                        @elseif($index == 1)
                                            <span class="badge badge-secondary" style="font-size: 16px; padding: 5px 12px; border-radius: 50%;">
                                                <i class="fas fa-medal"></i>
                                            </span>
                                        @elseif($index == 2)
                                            <span class="badge badge-danger" style="font-size: 16px; padding: 5px 12px; border-radius: 50%;">
                                                <i class="fas fa-medal"></i>
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-weight: 600;">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; vertical-align: middle;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1f75cb 0%, #3b5bd6 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 12px;">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #2c3e50;">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 20px; vertical-align: middle;" class="text-center">
                                        <span class="badge badge-info badge-pill p-2 px-3" style="font-size: 14px;">
                                            {{ $user->total_tasks }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px; vertical-align: middle;" class="text-center">
                                        <span class="badge badge-success badge-pill p-2 px-3" style="font-size: 14px;">
                                            {{ $user->completed_tasks }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px; vertical-align: middle;" class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress" style="height: 25px; width: 120px; border-radius: 12px; background: #e9ecef;">
                                                <div class="progress-bar bg-{{ $user->completion_rate >= 80 ? 'success' : ($user->completion_rate >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $user->completion_rate }}%; border-radius: 12px; transition: width 1s ease;">
                                                    <span style="font-size: 12px; font-weight: 600;">
                                                        {{ number_format($user->completion_rate, 1) }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No data available yet. Start assigning tasks to see performance metrics.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* Animations */
    .animated-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .animated-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 40px rgba(0,0,0,0.2) !important;
    }
    
    .animated-card .small-box-footer {
        transition: background 0.3s ease;
    }
    
    .animated-card:hover .small-box-footer {
        background: rgba(0,0,0,0.08);
    }
    
    /* Glass Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .glass-card:hover {
        box-shadow: 0 12px 48px rgba(0,0,0,0.12) !important;
    }
    
    /* Completion Ring */
    .completion-ring {
        position: relative;
        display: inline-block;
    }
    
    .completion-ring svg {
        display: block;
        transform: rotate(-90deg);
    }
    
    .completion-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    
    /* Avatar Circle */
    .avatar-circle {
        flex-shrink: 0;
        font-size: 14px;
        letter-spacing: 0.5px;
    }
    
    /* Badge Improvements */
    .badge-pill {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }
    
    /* Table Hover Effect */
    .table-hover tbody tr {
        transition: background 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
    }
    
    /* Counter Animation */
    .counter {
        font-weight: 700;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .completion-ring svg {
            width: 140px;
            height: 140px;
        }
        
        .completion-text h2 {
            font-size: 28px;
        }
        
        .avatar-circle {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter Animation
        document.querySelectorAll('.counter').forEach(counter => {
            const target = parseInt(counter.dataset.target);
            const duration = 1000;
            const step = Math.max(1, Math.floor(target / 60));
            let current = 0;
            
            const interval = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(interval);
                }
                counter.textContent = current.toLocaleString();
            }, 16);
        });
        
        // Project Status Chart
        const statusCtx = document.getElementById('projectStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Completed', 'Draft', 'Archived'],
                datasets: [{
                    data: [
                        {{ $activeProjects ?? 0 }},
                        {{ $completedProjects ?? 0 }},
                        {{ $draftProjects ?? 0 }},
                        {{ $archivedProjects ?? 0 }}
                    ],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#6c757d'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                        }
                    }
                },
                cutout: '65%',
            }
        });

        // Monthly Trend Chart
        const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        const monthlyData = @json($monthlyTrend ?? []);
        const labels = Object.keys(monthlyData).reverse();
        const values = Object.values(monthlyData).reverse();
        
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Projects Created',
                    data: values,
                    backgroundColor: labels.map((_, i) => {
                        const gradient = trendCtx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, '#1f75cb');
                        gradient.addColorStop(1, '#3b5bd6');
                        return gradient;
                    }),
                    borderColor: '#1f75cb',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });

        // Progress Distribution Chart
        const progressCtx = document.getElementById('progressDistributionChart').getContext('2d');
        const progressData = @json($progressDistribution ?? []);
        const progressLabels = Object.keys(progressData);
        const progressValues = Object.values(progressData);
        
        new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: progressLabels,
                datasets: [{
                    label: 'Projects',
                    data: progressValues,
                    backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#6c757d'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });
    });
</script>
@endpush