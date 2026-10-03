{{-- ICT Director dashboard: the whole office at a glance. --}}
@extends('layouts.console')
@section('title', 'Dashboard')

@section('content_header')
    <x-ui.page-header title="Director dashboard" description="Projects, teams and deadlines across the JU-ICT Team.">
        @can('view-reports')
            <x-ui.button variant="outline" :href="route('admin.analytics.index')">
                <i data-lucide="chart-column" class="size-4"></i>
                Reports
            </x-ui.button>
        @endcan
        @canvisit(route('admin.projects.create'))
            <x-ui.button :href="route('admin.projects.create')">
                <i data-lucide="plus" class="size-4"></i>
                New project
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Projects" :value="$projectsCount" icon="folder-kanban" :href="route('admin.projects.index')" link-text="View projects" />
        <x-ui.stat-card label="Tasks" :value="$tasksCount" icon="list-todo" :href="route('admin.tasks.index')" link-text="View tasks" />
        <x-ui.stat-card label="People" :value="$usersCount" icon="users" :href="route('admin.organization.teams.index')" link-text="View teams" />
        <x-ui.stat-card label="Overdue tasks" :value="$overdueTasks" icon="triangle-alert" :href="route('admin.tasks.index', ['overdue' => 1])" link-text="View overdue" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card title="Task status" description="Every task, by where it stands today.">
            <div class="relative h-64"><canvas id="taskStatusChart" role="img" aria-label="Task status chart"></canvas></div>
        </x-ui.card>
        <x-ui.card title="Projects by status" description="How many projects sit in each stage.">
            <div class="relative h-64"><canvas id="projectStatusChart" role="img" aria-label="Projects by status chart"></canvas></div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Teams at a glance" description="Workload and overdue work per team." flush>
        @if ($teams->isEmpty())
            <x-ui.empty-state icon="users" title="No teams yet" description="Create teams to see their workload here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted text-left text-[11px] uppercase tracking-[0.08em] text-muted-foreground">
                            <th class="px-5 py-2.5 font-semibold">Team</th>
                            <th class="px-5 py-2.5 font-semibold">Leader</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Members</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Projects</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Open tasks</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $row)
                            <tr class="border-b border-border/70 transition-colors duration-200 last:border-0 hover:bg-background">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.organization.teams.show', $row['team']) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $row['team']->name }}</a>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ $row['leader'] ?? 'No leader' }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['members'] }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['projects'] }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['open'] }}</td>
                                <td @class(['tabular px-5 py-3 text-right', 'font-semibold text-destructive' => $row['overdue'] > 0])>{{ $row['overdue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card title="Most overdue" description="The oldest missed deadlines across the office." flush>
            @include('dashboards.partials.task-list', ['tasks' => $overdueList, 'empty' => 'Nothing is overdue.'])
        </x-ui.card>

        <x-ui.card title="Recent projects" description="The latest projects created." flush>
            @if ($recentProjects->isEmpty())
                <x-ui.empty-state icon="inbox" title="No projects yet" />
            @else
                <ul class="m-0 list-none divide-y divide-border p-0">
                    @foreach ($recentProjects as $project)
                        <li class="flex items-center gap-4 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.projects.show', $project) }}" class="block truncate font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $project->name }}</a>
                                <p class="m-0 text-[13px] text-muted-foreground">{{ $project->created_at->diffForHumans() }} · {{ $project->creator->name ?? 'N/A' }}</p>
                            </div>
                            <x-ui.progress class="w-36 shrink-0" :value="$project->progress_percentage ?? 0" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var css = getComputedStyle(document.documentElement);
        var token = function (name) { return css.getPropertyValue(name).trim(); };
        Chart.defaults.font.family = token('--font-sans') || "'Source Sans 3', system-ui, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = token('--muted-foreground');
        Chart.defaults.borderColor = token('--border');

        new Chart(document.getElementById('taskStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Under review', 'In progress', 'Not started', 'Overdue'],
                datasets: [{
                    data: [{{ $completedTasks }}, {{ $reviewTasks }}, {{ $inProgressTasks }}, {{ $notStartedTasks }}, {{ $overdueTasks }}],
                    backgroundColor: [token('--success'), token('--warning'), token('--ju-blue'), token('--chart-5'), token('--destructive')],
                    borderWidth: 2, borderColor: '#ffffff',
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '72%',
                plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', boxWidth: 8 } } } }
        });

        new Chart(document.getElementById('projectStatusChart'), {
            type: 'bar',
            data: { labels: @json($statusLabels), datasets: [{ label: 'Projects', data: @json($statusData), backgroundColor: token('--ju-blue'), borderRadius: 6, maxBarThickness: 40 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
        });
    });
</script>
@endpush
