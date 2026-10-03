{{-- resources/views/admin/projects/show.blade.php --}}
@extends('layouts.console')

@section('title', $project->name)

@section('content_header')
    <a href="{{ route('admin.projects.index') }}"
       class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        All Projects
    </a>
    <x-ui.page-header :title="$project->name" :description="$project->template->name ?? 'Custom project'">
        @if ($project->status !== 'completed'
             && $project->phases->isNotEmpty()
             && $project->phases->every(fn ($p) => $p->progress_percentage >= 100)
             && (auth()->user()->isDirector() || $project->created_by === auth()->id()))
            @canvisit(route('admin.projects.close', $project), 'POST')
                <form method="POST" action="{{ route('admin.projects.close', $project) }}" class="m-0"
                      onsubmit="return confirm('All tasks are done. Close this project?')">
                    @csrf
                    <x-ui.button type="submit">
                        <i data-lucide="flag" class="size-4"></i>
                        Close project
                    </x-ui.button>
                </form>
            @endcanvisit
        @endif
        @canvisit(route('admin.projects.edit', $project))
            <x-ui.button variant="outline" :href="route('admin.projects.edit', $project)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit
            </x-ui.button>
        @endcanvisit
        @canvisit(route('admin.projects.phases.create', $project))
            <x-ui.button :href="route('admin.projects.phases.create', $project)">
                <i data-lucide="plus" class="size-4"></i>
                Add Phase
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
@php
    $statusVariant = ['active' => 'primary', 'completed' => 'success'][$project->status] ?? 'neutral';
    $money         = $project->budget_summary;

    // Aggregate task counts across all phases
    $totalTasks     = $project->phases->sum(fn($p) => $p->tasks->count());
    $completedTasks = $project->phases->sum(fn($p) => $p->tasks->where('progress_percentage', 100)->count());
    $blockedTasks   = $project->phases->sum(fn($p) => $p->tasks->filter(fn($t) => $t->status?->name === 'Blocked')->count());
    $overdueTasks   = $project->phases->sum(fn($p) => $p->tasks->filter(fn($t) => $t->isOverdue())->count());
    $overduePhases  = $project->phases->filter(fn($p) => $p->isOverdue())->count();
@endphp

{{-- ═══════════════════════════════════════════════════════════
     SECTION 1 — PROJECT OVERVIEW HERO
     ═══════════════════════════════════════════════════════════ --}}
<div class="rounded-xl border border-border bg-card shadow-sm">

    {{-- Top strip: status + key numbers --}}
    <div class="proj-hero-strip grid gap-px bg-border sm:grid-cols-2 lg:grid-cols-4" style="border-radius:0.75rem 0.75rem 0 0; overflow:hidden;">
        <div class="bg-card px-5 py-4">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Status</p>
            <div class="mt-2 flex items-center gap-2">
                <x-ui.badge :variant="$statusVariant">{{ ucfirst($project->status ?? 'Draft') }}</x-ui.badge>
            </div>
        </div>
        <div class="bg-card px-5 py-4">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Overall Progress</p>
            <p class="m-0 mt-1 text-2xl font-bold tabular-nums text-foreground">{{ round($project->progress_percentage ?? 0) }}<span class="text-base font-normal text-muted-foreground">%</span></p>
            <x-ui.progress class="mt-2" :value="$project->progress_percentage ?? 0" />
        </div>
        <div class="bg-card px-5 py-4">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Schedule</p>
            <p class="m-0 mt-1 text-[13px] font-semibold text-foreground">
                {{ $project->start_date?->format('M d, Y') ?? 'Not set' }}
            </p>
            <p class="m-0 text-[13px] text-muted-foreground">
                to {{ $project->end_date?->format('M d, Y') ?? 'Not set' }}
            </p>
        </div>
        <div class="bg-card px-5 py-4">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Created by</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-[12px] font-bold text-primary">
                    {{ strtoupper(substr($project->creator->name ?? 'U', 0, 1)) }}
                </span>
                <span class="text-[13px] font-medium text-foreground">{{ $project->creator->name ?? 'Unknown' }}</span>
            </div>
        </div>
    </div>

    {{-- Description / Objectives --}}
    @if ($project->description || $project->objectives)
        <div class="grid gap-6 border-t border-border px-5 py-4 sm:grid-cols-2">
            @if ($project->description)
                <div>
                    <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Description</p>
                    <p class="m-0 mt-1.5 text-[14px] leading-relaxed text-foreground">{{ $project->description }}</p>
                </div>
            @endif
            @if ($project->objectives)
                <div>
                    <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Objectives</p>
                    <p class="m-0 mt-1.5 whitespace-pre-line text-[14px] leading-relaxed text-foreground">{{ $project->objectives }}</p>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 2 — QUICK STATS ROW
     ═══════════════════════════════════════════════════════════ --}}
<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

    {{-- Tasks --}}
    <div class="proj-stat-tasks rounded-xl border border-border bg-card px-5 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Tasks</p>
            <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10">
                <i data-lucide="list-checks" class="size-4 text-primary"></i>
            </span>
        </div>
        <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-foreground">{{ $completedTasks }}<span class="text-base font-normal text-muted-foreground"> / {{ $totalTasks }}</span></p>
        <p class="m-0 mt-0.5 text-[12px] text-muted-foreground">completed</p>
    </div>

    {{-- Phases --}}
    <div class="proj-stat-phases rounded-xl border border-border bg-card px-5 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Phases</p>
            <span class="flex size-8 items-center justify-center rounded-lg bg-blue-500/10">
                <i data-lucide="layers" class="size-4 text-blue-500"></i>
            </span>
        </div>
        <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-foreground">{{ $project->phases->count() }}</p>
        <p class="m-0 mt-0.5 text-[12px] text-muted-foreground">
            {{ $overduePhases > 0 ? $overduePhases.' overdue' : 'all on track' }}
        </p>
    </div>

    {{-- Blocked --}}
    <div class="proj-stat-blocked rounded-xl border @if($blockedTasks > 0) border-destructive/30 bg-destructive/5 @else border-border bg-card @endif px-5 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Blocked</p>
            <span class="flex size-8 items-center justify-center rounded-lg @if($blockedTasks > 0) bg-destructive/10 @else bg-muted @endif">
                <i data-lucide="ban" class="size-4 @if($blockedTasks > 0) text-destructive @else text-muted-foreground @endif"></i>
            </span>
        </div>
        <p class="m-0 mt-2 text-2xl font-bold tabular-nums @if($blockedTasks > 0) text-destructive @else text-foreground @endif">{{ $blockedTasks }}</p>
        <p class="m-0 mt-0.5 text-[12px] text-muted-foreground">blocked tasks</p>
    </div>

    {{-- Budget --}}
    <div class="proj-stat-budget rounded-xl border border-border bg-card px-5 py-4 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="m-0 text-[12px] font-medium uppercase tracking-wider text-muted-foreground">Budget</p>
            <span class="flex size-8 items-center justify-center rounded-lg bg-green-500/10">
                <i data-lucide="coins" class="size-4 text-green-600"></i>
            </span>
        </div>
        <p class="m-0 mt-2 text-2xl font-bold tabular-nums text-foreground">
            @if ($money['budget'] !== null)
                {{ number_format($money['budget'] / 1000, 0) }}<span class="text-base font-normal text-muted-foreground">K ETB</span>
            @else
                <span class="text-base font-normal text-muted-foreground">Not set</span>
            @endif
        </p>
        @if ($money['used_percent'] !== null)
            <p class="m-0 mt-0.5 text-[12px] @if(($money['remaining'] ?? 1) < 0) text-destructive font-semibold @else text-muted-foreground @endif">
                {{ $money['used_percent'] }}% spent
            </p>
        @else
            <p class="m-0 mt-0.5 text-[12px] text-muted-foreground">{{ number_format($money['spent'], 0) }} ETB spent</p>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 3 — BUDGET DETAIL + CHARTS + TIMELINE
     ═══════════════════════════════════════════════════════════ --}}
<div class="mt-5">
    <x-ui.card title="Budget breakdown" description="Planned is the sum of task cost estimates. Spent is the sum of recorded actual costs.">
        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Budget</dt>
                <dd class="tabular m-0 mt-1 font-semibold">{{ $money['budget'] !== null ? number_format($money['budget'], 2).' ETB' : 'Not set' }}</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Planned (tasks)</dt>
                <dd class="tabular m-0 mt-1 font-semibold">{{ number_format($money['planned'], 2) }} ETB</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Spent</dt>
                <dd class="tabular m-0 mt-1 font-semibold">{{ number_format($money['spent'], 2) }} ETB</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Remaining</dt>
                <dd @class(['tabular m-0 mt-1 font-semibold', 'text-destructive' => ($money['remaining'] ?? 0) < 0])>
                    {{ $money['remaining'] !== null ? number_format($money['remaining'], 2).' ETB' : 'N/A' }}
                </dd>
            </div>
        </dl>
        @if ($money['used_percent'] !== null)
            <x-ui.progress class="mt-5" :value="min($money['used_percent'], 100)" />
            <p class="m-0 mt-2 text-[13px] text-muted-foreground">
                {{ $money['used_percent'] }}% of the budget spent
                @if ($money['planned'] > $money['budget']) — task estimates exceed the budget @endif
            </p>
        @endif
    </x-ui.card>
</div>

@include('admin.projects.partials.progress-stats')
@include('admin.projects.partials.progress-charts')
@include('admin.projects.partials.timeline')

{{-- ═══════════════════════════════════════════════════════════
     SECTION 4 — PHASES BREAKDOWN
     ═══════════════════════════════════════════════════════════ --}}
<div class="mt-8">

    {{-- Section heading --}}
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <h2 class="m-0 text-lg font-bold text-foreground">
                Project Phases
                <span class="ml-2 rounded-full bg-muted px-2.5 py-0.5 text-[13px] font-semibold text-muted-foreground">{{ $project->phases->count() }}</span>
            </h2>
            <p class="m-0 mt-0.5 text-[13px] text-muted-foreground">Detailed breakdown of every phase, its tasks, schedule and health.</p>
        </div>
        @canvisit(route('admin.projects.phases.create', $project))
            <x-ui.button variant="outline" :href="route('admin.projects.phases.create', $project)">
                <i data-lucide="plus" class="size-4"></i>
                Add Phase
            </x-ui.button>
        @endcanvisit
    </div>

    @if ($project->phases->isEmpty())
        <div class="rounded-xl border border-dashed border-border bg-card p-10 text-center">
            <i data-lucide="layers" class="mx-auto size-10 text-muted-foreground/40"></i>
            <p class="m-0 mt-3 font-semibold text-foreground">No phases yet</p>
            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Break the project into phases to start adding tasks.</p>
            @canvisit(route('admin.projects.phases.create', $project))
                <x-ui.button class="mt-4" :href="route('admin.projects.phases.create', $project)">
                    <i data-lucide="plus" class="size-4"></i>
                    Create first phase
                </x-ui.button>
            @endcanvisit
        </div>
    @else

        @foreach ($project->phases as $phaseIndex => $phase)
        @php
            $stats         = $phase->task_stats;
            $overdue       = $phase->isOverdue();
            $onTrack       = $phase->isOnTrack();
            $daysRemaining = $phase->days_remaining;
            $daysOverdue   = $phase->days_overdue;
            $isComplete    = $phase->progress_percentage >= 100;

            $accentBorder = $overdue
                ? 'border-l-destructive'
                : ($stats['blocked'] > 0
                    ? 'border-l-orange-400'
                    : ($isComplete
                        ? 'border-l-green-500'
                        : 'border-l-primary'));

            $priorityBg = [
                'Critical' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'High'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                'Medium'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                'Low'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            ];
            $taskStatusBg = [
                'Completed'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'In Progress'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                'Blocked'      => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'Under Review' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                'Not Started'  => 'bg-muted text-muted-foreground',
            ];
        @endphp

        {{-- Phase card --}}
        <div class="mb-4 overflow-hidden rounded-xl border border-l-4 border-border bg-card shadow-sm {{ $accentBorder }} @if($overdue) bg-destructive/[0.02] @endif">

            {{-- ── Phase header bar ── --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-3.5
                        @if($overdue) phase-hdr-overdue
                        @elseif($stats['blocked'] > 0) phase-hdr-blocked
                        @elseif($isComplete) phase-hdr-complete
                        @else phase-hdr-normal @endif">
                <div class="flex min-w-0 flex-wrap items-center gap-2.5">
                    {{-- Phase number bubble --}}
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[12px] font-bold text-primary">
                        {{ $phaseIndex + 1 }}
                    </span>
                    <a href="{{ route('admin.phases.show', $phase) }}"
                       class="text-[15px] font-bold text-foreground no-underline hover:text-primary hover:no-underline">
                        {{ $phase->name }}
                    </a>
                    <x-ui.badge :variant="$phase->status_color">{{ $phase->status?->name ?? 'N/A' }}</x-ui.badge>

                    {{-- Health pills --}}
                    @if ($overdue)
                        <span class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-0.5 text-[12px] font-semibold text-destructive">
                            <i data-lucide="triangle-alert" class="size-3"></i>
                            {{ $daysOverdue }}d overdue
                        </span>
                    @elseif ($daysRemaining !== null && $daysRemaining <= 7)
                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-[12px] font-semibold text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            <i data-lucide="clock" class="size-3"></i>
                            {{ $daysRemaining }}d left
                        </span>
                    @endif
                    @if (! $onTrack && ! $overdue && ! $isComplete)
                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2.5 py-0.5 text-[12px] font-semibold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                            <i data-lucide="trending-down" class="size-3"></i>
                            Behind schedule
                        </span>
                    @endif
                    @if ($isComplete)
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-[12px] font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <i data-lucide="check-circle-2" class="size-3"></i>
                            Completed
                        </span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex shrink-0 items-center gap-1.5">
                    @canvisit(route('admin.phases.edit', $phase))
                        <x-ui.icon-button icon="pencil" label="Edit phase" :href="route('admin.phases.edit', $phase)" />
                    @endcanvisit
                    @canvisit(route('admin.phases.destroy', $phase), 'DELETE')
                        <form action="{{ route('admin.phases.destroy', $phase) }}" method="POST" class="m-0"
                              onsubmit="return confirm('Delete this phase? All tasks will be deleted.')">
                            @csrf @method('DELETE')
                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete phase" tone="destructive" />
                        </form>
                    @endcanvisit
                    <x-ui.button size="sm" :href="route('admin.phases.show', $phase)">
                        Open phase
                        <i data-lucide="arrow-right" class="size-3.5"></i>
                    </x-ui.button>
                </div>
            </div>

            {{-- ── Phase body ── --}}
            <div class="p-5">

                {{-- Description --}}
                @if ($phase->description)
                    <p class="m-0 mb-4 text-[13px] text-muted-foreground">{{ Str::limit($phase->description, 180) }}</p>
                @endif

                {{-- ── Meta grid: progress + schedule + counts ── --}}
                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">

                    {{-- Progress (spans 2 cols on large) --}}
                    <div class="sm:col-span-2">
                        <p class="m-0 mb-1.5 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Progress</p>
                        <div class="flex items-center gap-3">
                            {{-- Donut-style circle --}}
                            <div class="relative shrink-0" style="width:52px;height:52px;">
                                <svg viewBox="0 0 36 36" class="size-full -rotate-90">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3.2"
                                            class="text-muted/60"/>
                                    <circle cx="18" cy="18" r="15.9" fill="none"
                                            stroke="{{ $isComplete ? '#22c55e' : ($overdue ? 'rgb(239 68 68)' : 'hsl(var(--primary))') }}"
                                            stroke-width="3.2"
                                            stroke-dasharray="{{ round($phase->progress_percentage, 1) }} {{ 100 - round($phase->progress_percentage, 1) }}"
                                            stroke-linecap="round"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold text-foreground">
                                    {{ round($phase->progress_percentage) }}%
                                </span>
                            </div>
                            <div class="flex flex-col gap-1 text-[12px]">
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="size-2 rounded-full bg-green-500"></span>
                                    {{ $stats['completed'] }} done
                                </span>
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="size-2 rounded-full bg-blue-500"></span>
                                    {{ $stats['in_progress'] }} in progress
                                </span>
                                <span class="flex items-center gap-1.5 text-muted-foreground">
                                    <span class="size-2 rounded-full bg-muted-foreground/30"></span>
                                    {{ $stats['not_started'] }} not started
                                </span>
                                @if ($stats['blocked'] > 0)
                                    <span class="flex items-center gap-1.5 font-semibold text-destructive">
                                        <span class="size-2 rounded-full bg-destructive"></span>
                                        {{ $stats['blocked'] }} blocked
                                    </span>
                                @endif
                            </div>
                        </div>
                        <x-ui.progress class="mt-3" :value="$phase->progress_percentage" />
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <p class="m-0 mb-1.5 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Schedule</p>
                        <div class="space-y-1 text-[13px]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar-check" class="size-3.5 shrink-0 text-muted-foreground"></i>
                                <span class="text-muted-foreground">Start:</span>
                                <span class="font-medium text-foreground">{{ $phase->start_date?->format('M d, Y') ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar-x" class="size-3.5 shrink-0 text-muted-foreground"></i>
                                <span class="text-muted-foreground">End:</span>
                                <span class="font-medium @if($overdue) text-destructive @else text-foreground @endif">
                                    {{ $phase->end_date?->format('M d, Y') ?? '—' }}
                                </span>
                            </div>
                            @if ($phase->estimated_days)
                                <div class="flex items-center gap-2">
                                    <i data-lucide="timer" class="size-3.5 shrink-0 text-muted-foreground"></i>
                                    <span class="text-muted-foreground">Est:</span>
                                    <span class="font-medium text-foreground">{{ $phase->estimated_days }}d</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tasks count --}}
                    <div>
                        <p class="m-0 mb-1.5 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Tasks</p>
                        <p class="m-0 text-2xl font-bold tabular-nums text-foreground">
                            {{ $stats['total'] }}
                        </p>
                        <p class="m-0 text-[12px] text-muted-foreground">total tasks</p>
                    </div>

                    {{-- Milestones count --}}
                    <div>
                        <p class="m-0 mb-1.5 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Milestones</p>
                        <p class="m-0 text-2xl font-bold tabular-nums text-foreground">
                            {{ $phase->milestones->count() }}
                        </p>
                        <p class="m-0 text-[12px] text-muted-foreground">
                            {{ $phase->milestones->filter(fn($m) => $m->completed_at)->count() }} completed
                        </p>
                    </div>
                </div>

                {{-- ── Tasks table ── --}}
                @if ($phase->tasks->isNotEmpty())
                    <div class="mt-5">
                        <p class="m-0 mb-2 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Tasks</p>
                        <div class="overflow-hidden rounded-lg border border-border">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr class="border-b border-border bg-muted/70">
                                        <th class="px-4 py-2.5 text-left text-[12px] font-semibold text-muted-foreground">Task name</th>
                                        <th class="px-4 py-2.5 text-left text-[12px] font-semibold text-muted-foreground">Status</th>
                                        <th class="px-4 py-2.5 text-left text-[12px] font-semibold text-muted-foreground">Priority</th>
                                        <th class="px-4 py-2.5 text-left text-[12px] font-semibold text-muted-foreground">Assignee</th>
                                        <th class="px-4 py-2.5 text-right text-[12px] font-semibold text-muted-foreground">Progress</th>
                                        <th class="px-4 py-2.5 text-right text-[12px] font-semibold text-muted-foreground">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/60">
                                    @foreach ($phase->tasks as $task)
                                        @php
                                            $tDone     = $task->progress_percentage >= 100;
                                            $tOverdue  = $task->isOverdue();
                                            $tPriority = $priorityBg[$task->priority?->name] ?? 'bg-muted text-muted-foreground';
                                            $tStatus   = $taskStatusBg[$task->status?->name] ?? 'bg-muted text-muted-foreground';
                                            $subCount  = $task->subtasks->count();
                                        @endphp
                                        <tr class="group hover:bg-muted/30 @if($tOverdue && !$tDone) bg-destructive/[0.03] @endif">
                                            {{-- Name --}}
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    @if ($tDone)
                                                        <i data-lucide="check-circle-2" class="size-4 shrink-0 text-green-500"></i>
                                                    @elseif ($task->status?->name === 'Blocked')
                                                        <i data-lucide="ban" class="size-4 shrink-0 text-destructive"></i>
                                                    @elseif ($task->status?->name === 'In Progress')
                                                        <i data-lucide="loader-circle" class="size-4 shrink-0 text-blue-500"></i>
                                                    @else
                                                        <i data-lucide="circle-dashed" class="size-4 shrink-0 text-muted-foreground/50"></i>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <a href="{{ route('admin.tasks.show', $task) }}"
                                                           class="block truncate font-medium no-underline hover:text-primary hover:no-underline
                                                                  @if($tDone) text-muted-foreground line-through @else text-foreground @endif">
                                                            {{ $task->name }}
                                                        </a>
                                                        @if ($subCount)
                                                            <span class="text-[11px] text-muted-foreground">{{ $subCount }} subtask{{ $subCount !== 1 ? 's' : '' }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Status --}}
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $tStatus }}">
                                                    {{ $task->status?->name ?? '—' }}
                                                </span>
                                            </td>
                                            {{-- Priority --}}
                                            <td class="px-4 py-3">
                                                @if ($task->priority)
                                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $tPriority }}">
                                                        <i data-lucide="flag" class="size-3"></i>
                                                        {{ $task->priority->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted-foreground">—</span>
                                                @endif
                                            </td>
                                            {{-- Assignee --}}
                                            <td class="px-4 py-3">
                                                @if ($task->assignee)
                                                    <div class="flex items-center gap-2">
                                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[11px] font-bold text-primary">
                                                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                                        </span>
                                                        <span class="truncate text-foreground">{{ Str::limit($task->assignee->name, 20) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-[12px] italic text-muted-foreground">Unassigned</span>
                                                @endif
                                            </td>
                                            {{-- Progress --}}
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-muted">
                                                        <div class="h-full rounded-full transition-all
                                                                    @if($tDone) bg-green-500
                                                                    @elseif($tOverdue) bg-destructive
                                                                    @else bg-primary @endif"
                                                             style="width:{{ $task->progress_percentage }}%">
                                                        </div>
                                                    </div>
                                                    <span class="w-9 text-right text-[12px] tabular-nums text-muted-foreground">
                                                        {{ (int)$task->progress_percentage }}%
                                                    </span>
                                                </div>
                                            </td>
                                            {{-- Deadline --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-[12px]
                                                       @if($tOverdue && !$tDone) font-semibold text-destructive @else text-muted-foreground @endif">
                                                @if ($task->deadline)
                                                    @if ($tOverdue && !$tDone)
                                                        <span class="mr-1"><i data-lucide="triangle-alert" class="inline size-3"></i></span>
                                                    @endif
                                                    {{ $task->deadline->format('M d, Y') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="mt-4 flex items-center gap-2 rounded-lg border border-dashed border-border px-4 py-3 text-[13px] text-muted-foreground">
                        <i data-lucide="inbox" class="size-4 shrink-0"></i>
                        No tasks in this phase yet.
                    </div>
                @endif

                {{-- ── Milestones ── --}}
                @if ($phase->milestones->isNotEmpty())
                    <div class="mt-5">
                        <p class="m-0 mb-2 text-[12px] font-semibold uppercase tracking-wider text-muted-foreground">Milestones</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($phase->milestones as $ms)
                                @php
                                    $msDone    = (bool) $ms->completed_at;
                                    $msOverdue = ! $msDone && $ms->due_date && $ms->due_date->isPast();
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[12px] font-medium
                                    @if($msDone)    border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400
                                    @elseif($msOverdue) border-destructive/40 bg-destructive/5 text-destructive
                                    @else           border-border bg-muted text-muted-foreground @endif">
                                    @if ($msDone)
                                        <i data-lucide="check-circle-2" class="size-3"></i>
                                    @elseif ($msOverdue)
                                        <i data-lucide="triangle-alert" class="size-3"></i>
                                    @else
                                        <i data-lucide="milestone" class="size-3"></i>
                                    @endif
                                    {{ $ms->title }}
                                    @if ($ms->due_date)
                                        <span class="opacity-60">· {{ $ms->due_date->format('M d') }}</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>{{-- end phase body --}}
        </div>{{-- end phase card --}}

        @endforeach
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 5 — FILES
     ═══════════════════════════════════════════════════════════ --}}
@can('view-project-files')
    @include('admin.projects.partials.files')
@endcan

@endsection

@push('css')
<style>
/* ── Project detail page — saturated accent treatment ──────────────────────
   Goal: vivid but not loud. Rich icon badges, a tinted hero strip, and
   coloured phase header gradients. Everything else stays white/neutral.
   ─────────────────────────────────────────────────────────────────────── */

/* ── Hero overview strip ─────────────────────────────────────────────────
   The four-cell grid at the top. Give the whole strip a deep-blue base so
   the numbers pop, then let the bg-card cells sit on top.                 */
.proj-hero-strip {
    background: linear-gradient(135deg, #003d7a 0%, #0067B8 60%, #1a8fd1 100%);
    border-radius: 0.75rem 0.75rem 0 0;
    overflow: hidden;
}
/* Each cell gets a frosted-glass look */
.proj-hero-strip > div {
    background: rgba(255,255,255,0.06) !important;
    backdrop-filter: blur(2px);
}
.proj-hero-strip > div:hover {
    background: rgba(255,255,255,0.10) !important;
}
/* All text inside the strip → white scale */
.proj-hero-strip p,
.proj-hero-strip span:not([class*="badge"]) {
    color: rgba(255,255,255,0.75) !important;
}
.proj-hero-strip .text-foreground,
.proj-hero-strip .font-bold { color: #fff !important; }
.proj-hero-strip .text-muted-foreground { color: rgba(255,255,255,0.60) !important; }
/* Progress bar track */
.proj-hero-strip [role="progressbar"] { background: rgba(255,255,255,0.18) !important; }
/* Status badge */
.proj-hero-strip [class*="rounded-full"][class*="border"] {
    background: rgba(255,255,255,0.15) !important;
    border-color: rgba(255,255,255,0.30) !important;
    color: #fff !important;
}
/* Creator avatar */
.proj-hero-strip .rounded-full:not([class*="border"]) {
    background: rgba(255,255,255,0.18) !important;
    color: #fff !important;
}

/* ── Quick stat card icon badges ─────────────────────────────────────────
   The size-8 rounded-lg icon holder in each card.                        */
/* Tasks card  — JU blue */
.proj-stat-tasks  > div:first-child > span.rounded-lg {
    background: linear-gradient(135deg,#0056a3,#0067B8) !important;
    box-shadow: 0 2px 8px rgba(0,103,184,0.30);
}
.proj-stat-tasks  > div:first-child > span.rounded-lg i { color: #fff !important; }

/* Phases card — cyan-teal */
.proj-stat-phases > div:first-child > span.rounded-lg {
    background: linear-gradient(135deg,#0e7490,#0891b2) !important;
    box-shadow: 0 2px 8px rgba(8,145,178,0.30);
}
.proj-stat-phases > div:first-child > span.rounded-lg i { color: #fff !important; }

/* Blocked card — vivid red */
.proj-stat-blocked > div:first-child > span.rounded-lg {
    background: linear-gradient(135deg,#b91c1c,#ef4444) !important;
    box-shadow: 0 2px 8px rgba(239,68,68,0.30);
}
.proj-stat-blocked > div:first-child > span.rounded-lg i { color: #fff !important; }

/* Budget card — emerald green */
.proj-stat-budget > div:first-child > span.rounded-lg {
    background: linear-gradient(135deg,#15803d,#22c55e) !important;
    box-shadow: 0 2px 8px rgba(34,197,94,0.28);
}
.proj-stat-budget > div:first-child > span.rounded-lg i { color: #fff !important; }

/* ── Phase card header bars ──────────────────────────────────────────────
   Replace the flat bg-muted/50 with a colour-coded left-to-right gradient
   matching each phase's state.                                            */
.phase-hdr-normal   { background: linear-gradient(to right,rgba(0,103,184,0.09),rgba(0,103,184,0.02)) !important; border-bottom-color: rgba(0,103,184,0.14) !important; }
.phase-hdr-complete { background: linear-gradient(to right,rgba(34,197,94,0.09),rgba(34,197,94,0.02)) !important; border-bottom-color: rgba(34,197,94,0.16) !important; }
.phase-hdr-overdue  { background: linear-gradient(to right,rgba(239,68,68,0.09),rgba(239,68,68,0.02)) !important; border-bottom-color: rgba(239,68,68,0.16) !important; }
.phase-hdr-blocked  { background: linear-gradient(to right,rgba(249,115,22,0.09),rgba(249,115,22,0.02)) !important; border-bottom-color: rgba(249,115,22,0.16) !important; }
</style>
@endpush
