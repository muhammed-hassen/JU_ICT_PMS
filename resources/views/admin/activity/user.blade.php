{{-- resources/views/admin/activity/user.blade.php --}}
@extends('layouts.console')

@section('title', "Activity - {$user->name}")

@section('content_header')
<<<<<<< HEAD
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-user-circle text-primary"></i>
            Activity: {{ $user->name }}
            <small>{{ $user->email }}</small>
        </h1>
        <a href="{{ route('admin.activity.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Activities
        </a>
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
                    <h3>{{ $stats['projects'] }}</h3>
                    <p>Project Activities</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['tasks'] }}</h3>
                    <p>Task Activities</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Activities --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-stream"></i>
                Recent Activities
            </h3>
        </div>
        <div class="card-body p-0">
            @if($activities->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No activities found for this user.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($activities as $activity)
                        <div>
                            <i class="fas {{ $activity->icon }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i>
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                                <h3 class="timeline-header">
                                    <span class="badge badge-{{ $activity->color }}">
                                        {{ $activity->action_label }}
                                    </span>
                                    <span class="badge badge-secondary">
                                        {{ $activity->type_label }}
                                    </span>
                                </h3>
                                <div class="timeline-body">
                                    <p>{{ $activity->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="card-footer clearfix">
            {{ $activities->links() }}
        </div>
    </div>
@endsection
=======
    <x-ui.page-header :title="'Activity: ' . $user->name" :description="$user->email">
        @canvisit(route('admin.activity.index'))
            <x-ui.button variant="outline" :href="route('admin.activity.index')">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to all activities
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total activities" :value="$stats['total']" icon="history" />
        <x-ui.stat-card label="Today" :value="$stats['today']" icon="calendar" />
        <x-ui.stat-card label="Project activities" :value="$stats['projects']" icon="folder-kanban" />
        <x-ui.stat-card label="Task activities" :value="$stats['tasks']" icon="list-checks" />
    </div>

    <x-ui.card class="mt-6" title="Recent activities" flush>
        @if ($activities->isEmpty())
            <x-ui.empty-state icon="inbox" title="No activities found for this user." />
        @else
            @include('admin.activity._feed', [
                'activities' => $activities,
                'showUser' => false,
                'showDetails' => false,
                'groupByDate' => false,
                'relativeTime' => true,
            ])
        @endif

        @if ($activities->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $activities->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
>>>>>>> 76e71c94e81bc2c6a2e2a82c33998d4388b7021a
