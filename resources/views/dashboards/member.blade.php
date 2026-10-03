{{-- Team Member dashboard: only the member's own work. --}}
@extends('layouts.console')
@section('title', 'Dashboard')

@section('content_header')
    <x-ui.page-header title="My dashboard" description="Your tasks and the projects you work on.">
        <x-ui.button :href="route('admin.tasks.my')">
            <i data-lucide="list-todo" class="size-4"></i>
            My tasks
        </x-ui.button>
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Open tasks" :value="$openCount" icon="list-todo" :href="route('admin.tasks.my')" link-text="View my tasks" />
        <x-ui.stat-card label="In progress" :value="$inProgressCount" icon="loader" />
        <x-ui.stat-card label="Due in the next 7 days" :value="$dueThisWeekCount" icon="calendar-clock" />
        <x-ui.stat-card label="Overdue" :value="$overdueCount" icon="triangle-alert" :hint="$completedCount . ' completed so far'" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card title="Up next" description="Your open tasks, nearest deadline first." flush>
            @include('dashboards.partials.task-list', ['tasks' => $upNext, 'empty' => 'You have no open tasks.', 'showAssignee' => false])
        </x-ui.card>

        <x-ui.card title="Recently assigned to you" description="The latest tasks given to you." flush>
            @if ($recentlyAssigned->isEmpty())
                <p class="m-0 px-5 py-8 text-center text-sm text-muted-foreground">Nothing has been assigned to you yet.</p>
            @else
                <ul class="m-0 list-none divide-y divide-border p-0">
                    @foreach ($recentlyAssigned as $task)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="block truncate font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $task->title }}</a>
                                <p class="m-0 truncate text-[13px] text-muted-foreground">{{ $task->phase->project->name ?? 'No project' }}</p>
                            </div>
                            <span class="shrink-0 text-xs text-muted-foreground">{{ $task->pivot->assigned_at ? \Illuminate\Support\Carbon::parse($task->pivot->assigned_at)->diffForHumans() : '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="My projects" description="Projects that contain your tasks." flush>
        @if ($projects->isEmpty())
            <x-ui.empty-state icon="folder-kanban" title="No projects yet" description="When you are given a task, its project shows up here." />
        @else
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($projects as $project)
                    <li class="flex items-center gap-4 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <span class="block truncate font-semibold text-foreground">{{ $project->name }}</span>
                            <p class="m-0 text-[13px] text-muted-foreground">{{ ucfirst($project->status ?? 'draft') }} @if ($project->end_date) · ends {{ $project->end_date->format('M j, Y') }} @endif</p>
                        </div>
                        <x-ui.progress class="w-40 shrink-0" :value="$project->progress_percentage ?? 0" />
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
@endsection
