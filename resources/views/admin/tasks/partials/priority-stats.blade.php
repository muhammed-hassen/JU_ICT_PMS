<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@php
    // Convert to collection if it's a paginator
    if ($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $tasksCollection = $tasks->getCollection();
    } elseif ($tasks instanceof \Illuminate\Database\Eloquent\Collection) {
        $tasksCollection = $tasks;
    } else {
        $tasksCollection = collect($tasks);
    }
    
    $priorityStats = [
        'Critical' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Critical'; })->count(),
        'High' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'High'; })->count(),
        'Medium' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Medium'; })->count(),
        'Low' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Low'; })->count(),
    ];
    $total = array_sum($priorityStats);
    
    $deadlineStats = [
        'overdue' => $tasksCollection->filter(function($t) { return $t->isOverdue(); })->count(),
        'due_today' => $tasksCollection->filter(function($t) { 
            return $t->deadline && $t->deadline->isToday() && !$t->isOverdue(); 
        })->count(),
        'due_soon' => $tasksCollection->filter(function($t) { 
            return $t->deadline && $t->deadline->diffInDays(now()) <= 3 && !$t->isOverdue(); 
        })->count(),
        'on_track' => $tasksCollection->filter(function($t) { 
            return $t->deadline && $t->deadline->diffInDays(now()) > 3 && !$t->isOverdue(); 
        })->count(),
        'no_deadline' => $tasksCollection->whereNull('deadline')->count(),
    ];
    
    // Get top 5 urgent tasks
    $urgentTasks = $tasksCollection->filter(function($t) {
        return $t->deadline && !$t->isOverdue() && $t->deadline->diffInDays(now()) <= 3;
    })->sortBy('deadline')->take(5);
@endphp

<div class="row mb-3">
    {{-- Priority Distribution --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">
                        <i class="fas fa-chart-pie text-primary"></i> Priority Distribution
                    </span>
                    <span class="text-muted">Total: {{ $total }} tasks</span>
                </div>
                <div class="progress mt-2" style="height: 30px;">
                    @foreach($priorityStats as $level => $count)
                        @php
                            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            $colors = [
                                'Critical' => 'danger',
                                'High' => 'warning',
                                'Medium' => 'info',
                                'Low' => 'success',
                            ];
                        @endphp
                        @if($percentage > 0)
                            <div class="progress-bar bg-{{ $colors[$level] }} progress-bar-striped" 
                                 role="progressbar" 
                                 style="width: {{ $percentage }}%"
                                 title="{{ $level }}: {{ $count }} tasks">
                                @if($percentage > 5)
                                    {{ $level }} ({{ $percentage }}%)
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="d-flex flex-wrap justify-content-between mt-2">
                    @foreach($priorityStats as $level => $count)
                        @php
                            $colors = [
                                'Critical' => 'danger',
                                'High' => 'warning',
                                'Medium' => 'info',
                                'Low' => 'success',
                            ];
                        @endphp
                        <span>
                            <span class="badge badge-{{ $colors[$level] }}">
                                {{ $level }}
                            </span>
                            {{ $count }} tasks
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Deadline Status --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">
                        <i class="fas fa-clock text-warning"></i> Deadline Status
                    </span>
                    <span class="text-muted">Total: {{ array_sum($deadlineStats) }} tasks</span>
                </div>
                <div class="mt-2">
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex justify-content-between">
                                <span><span class="badge badge-danger">Overdue</span></span>
                                <span class="font-weight-bold">{{ $deadlineStats['overdue'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge badge-warning">Due Today</span></span>
                                <span class="font-weight-bold">{{ $deadlineStats['due_today'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge badge-info">Due Soon</span></span>
                                <span class="font-weight-bold">{{ $deadlineStats['due_soon'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between">
                                <span><span class="badge badge-success">On Track</span></span>
                                <span class="font-weight-bold">{{ $deadlineStats['on_track'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge badge-secondary">No Deadline</span></span>
                                <span class="font-weight-bold">{{ $deadlineStats['no_deadline'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Urgent Tasks --}}
    @if($urgentTasks->count() > 0)
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Urgent Tasks (Due in 3 days)
                        <span class="badge badge-warning ml-2">{{ $urgentTasks->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($urgentTasks as $task)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.tasks.show', $task) }}">
                                    {{ $task->title }}
                                </a>
                                <span>
                                    <span class="badge badge-{{ $task->priority_color }}">
                                        {{ $task->priority->name ?? 'None' }}
                                    </span>
                                    <span class="badge badge-warning ml-1">
                                        <i class="fas fa-calendar"></i> 
                                        {{ $task->deadline->diffInDays(now()) }} days left
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>