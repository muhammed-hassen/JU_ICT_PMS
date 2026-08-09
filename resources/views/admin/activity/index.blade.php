{{-- resources/views/admin/activity/index.blade.php --}}
@extends('adminlte::page')
<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@section('title', 'Activity Log')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-history text-primary"></i>
            Activity Log
            <small>Track all system activities</small>
        </h1>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>
@endsection

@section('content')
    {{-- Stats --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Activities</p>
                </div>
                <div class="icon">
                    <i class="fas fa-history"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['today'] }}</h3>
                    <p>Today</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['this_week'] }}</h3>
                    <p>This Week</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['this_month'] }}</h3>
                    <p>This Month</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity.index') }}" class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>User</label>
                        <select name="user" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Action</label>
                        <select name="action" class="form-control">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst($action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.activity.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Activity Feed --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-stream"></i>
                Activity Feed
                <span class="badge badge-primary ml-2">{{ $activities->total() }}</span>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($activities->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No activities found.</p>
                </div>
            @else
                <div class="timeline">
                    @php $lastDate = null; @endphp
                    @foreach($activities as $activity)
                        @php
                            $currentDate = $activity->created_at->format('Y-m-d');
                            $dateLabel = '';
                            if ($currentDate != $lastDate) {
                                if ($activity->created_at->isToday()) {
                                    $dateLabel = 'Today';
                                } elseif ($activity->created_at->isYesterday()) {
                                    $dateLabel = 'Yesterday';
                                } else {
                                    $dateLabel = $activity->created_at->format('M d, Y');
                                }
                                $lastDate = $currentDate;
                            }
                        @endphp

                        @if($dateLabel)
                            <div class="time-label">
                                <span class="bg-{{ $activity->color }}">{{ $dateLabel }}</span>
                            </div>
                        @endif

                        <div>
                            <i class="fas {{ $activity->icon }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i>
                                    {{ $activity->created_at->format('h:i A') }}
                                </span>
                                <h3 class="timeline-header">
                                    <a href="{{ route('admin.activity.user', $activity->user) }}">
                                        <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                    </a>
                                    <span class="badge badge-{{ $activity->color }}">
                                        {{ $activity->action_label }}
                                    </span>
                                    <span class="badge badge-secondary">
                                        {{ $activity->type_label }}
                                    </span>
                                </h3>
                                <div class="timeline-body">
                                    <p>{{ $activity->description }}</p>
                                    @if($activity->properties)
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" 
                                                    data-target="#activity-{{ $activity->id }}">
                                                <i class="fas fa-code"></i> View Details
                                            </button>
                                            <div class="collapse mt-2" id="activity-{{ $activity->id }}">
                                                <pre class="bg-light p-2 rounded" style="font-size: 12px;">
                                                    {{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}
                                                </pre>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="timeline-footer">
                                    @if($activity->loggable)
                                        <a href="{{ route('admin.activities.show', $activity) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="card-footer clearfix">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@push('css')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline > div {
    margin-bottom: 15px;
}
.timeline > div > i {
    position: absolute;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.timeline-item {
    margin-left: 50px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
.timeline-item .time {
    float: right;
    color: #6c757d;
    font-size: 0.9rem;
}
.timeline-item .timeline-header {
    margin-top: 0;
    font-size: 1rem;
}
.timeline-item .timeline-body {
    margin-top: 10px;
}
.timeline-item .timeline-footer {
    margin-top: 10px;
}
.time-label {
    padding: 5px 0;
}
.time-label span {
    padding: 5px 15px;
    border-radius: 20px;
    color: #fff;
    font-weight: 600;
}
.bg-success { background-color: #28a745 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-info { background-color: #17a2b8 !important; }
.bg-primary { background-color: #007bff !important; }
.bg-secondary { background-color: #6c757d !important; }
</style>
@endpush