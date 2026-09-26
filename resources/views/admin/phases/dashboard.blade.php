@extends('layouts.console')

@section('title', 'Phase Management')

@section('content_header')
    <x-ui.page-header title="Phase Management" description="Overview of all project phases and milestones.">
        @canvisit(route('admin.templates.index'))
            <x-ui.button variant="outline" :href="route('admin.templates.index')">
                <i data-lucide="copy" class="size-4"></i>
                Manage Templates
            </x-ui.button>
        @endcanvisit
        @canvisit(route('admin.projects.index'))
            <x-ui.button variant="outline" :href="route('admin.projects.index')">
                <i data-lucide="list" class="size-4"></i>
                All Projects
            </x-ui.button>
        @endcanvisit
        @canvisit(route('admin.projects.create'))
            <x-ui.button :href="route('admin.projects.create')">
                <i data-lucide="plus" class="size-4"></i>
                New Project
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total Projects" :value="$totals['projects']" icon="folder-kanban" :href="route('admin.projects.index')" link-text="View all projects" />
        <x-ui.stat-card label="Total Phases" :value="$totals['phases']" icon="layers" />
        <x-ui.stat-card label="Total Tasks" :value="$totals['tasks']" icon="list-todo" />
        <x-ui.stat-card label="Completed Phases" :value="$totals['completedPhases']" icon="circle-check" />
    </div>

    <x-ui.card flush class="mt-6">
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Projects with Phases</h3>
            <x-ui.badge>{{ $projects->total() }} Projects</x-ui.badge>
        </div>

        @if ($projects->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th class="w-10">#</x-ui.th>
                            <x-ui.th>Project</x-ui.th>
                            <x-ui.th class="text-right">Phases</x-ui.th>
                            <x-ui.th class="text-right">Tasks</x-ui.th>
                            <x-ui.th class="min-w-44">Progress</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $index => $project)
                            @php
                                $statusVariant = [
                                    'active' => 'primary',
                                    'completed' => 'success',
                                ][$project->status] ?? 'neutral';
                            @endphp
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="tabular text-muted-foreground">{{ $projects->firstItem() + $index }}</x-ui.td>
                                <x-ui.td class="min-w-56">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-[13px] font-semibold text-foreground" aria-hidden="true">
                                            {{ strtoupper(substr($project->name, 0, 2)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-foreground">{{ $project->name }}</span>
                                            @if ($project->description)
                                                <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($project->description, 60) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $project->phases_count }}</x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $project->phases->sum(fn ($p) => $p->tasks->count()) }}</x-ui.td>
                                <x-ui.td><x-ui.progress :value="$project->progress_percentage ?? 0" /></x-ui.td>
                                <x-ui.td><x-ui.badge :variant="$statusVariant">{{ ucfirst($project->status) }}</x-ui.badge></x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.projects.show', $project))
                                            <x-ui.icon-button icon="eye" label="View Project" :href="route('admin.projects.show', $project)" />
                                        @endcanvisit
                                        @canvisit(route('admin.projects.phases.index', $project))
                                            <x-ui.icon-button icon="layers" label="Manage Phases" :href="route('admin.projects.phases.index', $project)" />
                                        @endcanvisit
                                        @canvisit(route('admin.projects.phases.create', $project))
                                            <x-ui.icon-button icon="plus" label="Add Phase" :href="route('admin.projects.phases.create', $project)" />
                                        @endcanvisit
                                        @canvisit(route('admin.projects.edit', $project))
                                            <x-ui.icon-button icon="pencil" label="Edit Project" :href="route('admin.projects.edit', $project)" />
                                        @endcanvisit
                                    </div>
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state icon="inbox" title="No Projects Found" description="Create your first project to start managing phases.">
                @canvisit(route('admin.projects.create'))
                    <x-ui.button :href="route('admin.projects.create')">
                        <i data-lucide="plus" class="size-4"></i>
                        Create Project
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @endif

        @if ($projects->hasPages())
            <x-slot:footer>
                <div class="flex w-full flex-wrap items-center justify-between gap-3">
                    <span class="tabular text-muted-foreground">
                        Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects
                    </span>
                    <div>{{ $projects->links() }}</div>
                </div>
            </x-slot:footer>
        @endif
    </x-ui.card>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <x-ui.stat-card label="Active Projects" :value="$totals['byStatus']['active'] ?? 0" icon="rocket" />
        <x-ui.stat-card label="Completed Projects" :value="$totals['byStatus']['completed'] ?? 0" icon="check-check" />
        <x-ui.stat-card label="Draft Projects" :value="$totals['byStatus']['draft'] ?? 0" icon="clock" />
    </div>
@endsection
