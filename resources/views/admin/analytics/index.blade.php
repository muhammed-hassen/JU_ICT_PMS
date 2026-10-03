{{-- Reports. Same components as the dashboard: nothing lifts, grows or counts up on hover or load. --}}
@extends('layouts.console')
@section('title', 'Reports')

@section('content_header')
    <x-ui.page-header title="Reports" :description="'How projects and people are performing. Scope: ' . $scopeLabel . '.'">
        @can('export-reports')
            <x-ui.button variant="outline" :href="route('admin.analytics.export.excel')">
                <i data-lucide="file-spreadsheet" class="size-4"></i>
                Export Excel
            </x-ui.button>
            <x-ui.button :href="route('admin.analytics.export.pdf')">
                <i data-lucide="file-down" class="size-4"></i>
                Export PDF
            </x-ui.button>
        @endcan
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total projects" :value="$totalProjects ?? 0" icon="folder-kanban"
                        :href="route('admin.projects.index')" link-text="View projects" />
        <x-ui.stat-card label="Active projects" :value="$activeProjects ?? 0" icon="circle-play" />
        <x-ui.stat-card label="Completed projects" :value="$completedProjects ?? 0" icon="circle-check" />
        <x-ui.stat-card label="Average project length" :value="number_format($avgCompletionTime ?? 0, 1) . ' days'" icon="clock"
                        hint="Planned start to end date" />
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        <x-ui.card title="Projects by status" description="Where every project stands today.">
            <div class="relative h-64">
                <canvas id="projectStatusChart" role="img" aria-label="Projects by status chart"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card title="New projects per month" description="The last 12 months.">
            <div class="relative h-64">
                <canvas id="monthlyTrendChart" role="img" aria-label="New projects per month chart"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card title="Project progress" description="How many projects sit in each progress band.">
            <div class="relative h-64">
                <canvas id="progressDistributionChart" role="img" aria-label="Project progress chart"></canvas>
            </div>
        </x-ui.card>

        <x-ui.card title="Task completion" description="Completed tasks out of all tasks.">
            @php $rate = max(0, min(100, (float) ($completionRate ?? 0))); @endphp
            <div class="flex h-64 flex-col items-center justify-center gap-5">
                <div class="relative size-40">
                    <svg viewBox="0 0 36 36" class="size-40 -rotate-90" aria-hidden="true">
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="var(--muted)" stroke-width="3" />
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="var(--success)" stroke-width="3"
                                stroke-linecap="round" stroke-dasharray="{{ $rate }} 100" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="tabular text-3xl font-semibold text-foreground">{{ number_format($rate, 1) }}%</span>
                        <span class="text-xs text-muted-foreground">complete</span>
                    </div>
                </div>
                <p class="tabular m-0 text-sm text-muted-foreground">
                    <span class="font-semibold text-foreground">{{ $completedTasks ?? 0 }}</span> of {{ $totalTasks ?? 0 }} tasks done
                </p>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Projects" description="Actual against planned progress, budget use, tasks and overdue work per project. Planned assumes work runs evenly from start to end date." flush>
        @if (count($projectRows))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted text-left text-[11px] uppercase tracking-[0.08em] text-muted-foreground">
                            <th class="px-5 py-2.5 font-semibold">Project</th>
                            <th class="px-5 py-2.5 font-semibold">Status</th>
                            <th class="px-5 py-2.5 font-semibold">Progress</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Planned</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Spent / budget (ETB)</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Phases</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Tasks done</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projectRows as $row)
                            <tr class="border-b border-border/70 transition-colors duration-200 last:border-0 hover:bg-background">
                                <td class="px-5 py-3 font-semibold text-foreground">{{ $row['name'] }}</td>
                                <td class="px-5 py-3"><x-ui.badge>{{ $row['status'] }}</x-ui.badge></td>
                                <td class="min-w-44 px-5 py-3"><x-ui.progress :value="$row['progress']" /></td>
                                <td @class(['tabular whitespace-nowrap px-5 py-3 text-right', 'font-semibold text-destructive' => $row['planned'] !== null && $row['progress'] < $row['planned'] - 10])>
                                    {{ $row['planned'] !== null ? $row['planned'] . '%' : 'No dates' }}
                                </td>
                                <td @class(['tabular whitespace-nowrap px-5 py-3 text-right', 'font-semibold text-destructive' => $row['budget'] !== null && $row['spent'] > $row['budget']])>
                                    {{ number_format($row['spent']) }} / {{ $row['budget'] !== null ? number_format($row['budget']) : 'not set' }}
                                </td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['phases'] }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $row['completed'] }} / {{ $row['tasks'] }}</td>
                                <td @class(['tabular px-5 py-3 text-right', 'font-semibold text-destructive' => $row['overdue'] > 0])>{{ $row['overdue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state icon="folder-kanban" title="No projects yet" description="Projects you can see will be listed here." />
        @endif
    </x-ui.card>

    <x-ui.card class="mt-6" title="Top performers" description="People with the most completed tasks." flush>
        @if (isset($topPerformers) && $topPerformers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted text-left text-[11px] uppercase tracking-[0.08em] text-muted-foreground">
                            <th class="w-12 px-5 py-2.5 font-semibold">#</th>
                            <th class="px-5 py-2.5 font-semibold">Person</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Tasks</th>
                            <th class="px-5 py-2.5 text-right font-semibold">Completed</th>
                            <th class="px-5 py-2.5 font-semibold">Completion rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topPerformers as $index => $person)
                            <tr class="border-b border-border/70 transition-colors duration-200 last:border-0 hover:bg-background">
                                <td class="tabular px-5 py-3 font-semibold text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted text-[13px] font-semibold text-ju-navy">
                                            {{ \Illuminate\Support\Str::of($person->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-foreground">{{ $person->name }}</span>
                                            <span class="block text-[13px] text-muted-foreground">{{ $person->email }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="tabular px-5 py-3 text-right">{{ $person->total_tasks }}</td>
                                <td class="tabular px-5 py-3 text-right">{{ $person->completed_tasks }}</td>
                                <td class="min-w-44 px-5 py-3"><x-ui.progress :value="$person->completion_rate" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state icon="inbox" title="No data yet" description="Assign tasks to see how people are doing." />
        @endif
    </x-ui.card>
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

        var bars = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
            }
        };

        new Chart(document.getElementById('projectStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Completed', 'Draft', 'Archived'],
                datasets: [{
                    data: [{{ $activeProjects ?? 0 }}, {{ $completedProjects ?? 0 }}, {{ $draftProjects ?? 0 }}, {{ $archivedProjects ?? 0 }}],
                    backgroundColor: [token('--ju-blue'), token('--success'), token('--chart-5'), token('--muted-foreground')],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', boxWidth: 8 } } }
            }
        });

        var monthly = @json($monthlyTrend ?? []);
        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(monthly).reverse(),
                datasets: [{ label: 'Projects', data: Object.values(monthly).reverse(), backgroundColor: token('--ju-blue'), borderRadius: 6, maxBarThickness: 40 }]
            },
            options: bars
        });

        var progress = @json($progressDistribution ?? []);
        new Chart(document.getElementById('progressDistributionChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(progress),
                datasets: [{ label: 'Projects', data: Object.values(progress), backgroundColor: token('--ju-blue'), borderRadius: 6, maxBarThickness: 40 }]
            },
            options: bars
        });
    });
</script>
@endpush
