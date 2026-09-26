{{-- resources/views/admin/projects/show.blade.php --}}
@extends('layouts.console')

@section('title', $project->name)

@section('content_header')
    <a href="{{ route('admin.projects.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Projects
    </a>
    <x-ui.page-header :title="$project->name" :description="$project->template->name ?? 'Custom project'">
        @if ($project->status !== 'completed' && $project->phases->isNotEmpty() && $project->phases->every(fn ($p) => $p->progress_percentage >= 100)
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
                Add phase
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    @php
        $statusVariant = ['active' => 'primary', 'completed' => 'success'][$project->status] ?? 'neutral';
    @endphp

    {{-- Overview --}}
    <x-ui.card>
        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Status</dt>
                <dd class="m-0 mt-1.5"><x-ui.badge :variant="$statusVariant">{{ ucfirst($project->status ?? 'Draft') }}</x-ui.badge></dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Progress</dt>
                <dd class="m-0 mt-1.5"><x-ui.progress :value="$project->progress_percentage ?? 0" /></dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Schedule</dt>
                <dd class="tabular m-0 mt-1 font-medium">
                    {{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}
                    <span class="text-muted-foreground">to</span>
                    {{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Created by</dt>
                <dd class="m-0 mt-1 font-medium">{{ $project->creator->name ?? 'Unknown' }}</dd>
            </div>
        </dl>
        @if ($project->description)
            <div class="mt-5 border-t border-border pt-4">
                <p class="m-0 text-[13px] text-muted-foreground">Description</p>
                <p class="m-0 mt-1 max-w-3xl">{{ $project->description }}</p>
            </div>
        @endif
        @if ($project->objectives)
            <div class="mt-5 border-t border-border pt-4">
                <p class="m-0 text-[13px] text-muted-foreground">Objectives</p>
                <p class="m-0 mt-1 max-w-3xl whitespace-pre-line">{{ $project->objectives }}</p>
            </div>
        @endif
    </x-ui.card>

    {{-- Budget: the project budget against task estimates and recorded costs --}}
    @php $money = $project->budget_summary; @endphp
    <x-ui.card class="mt-6" title="Budget and resources" description="Planned is the sum of task cost estimates. Spent is the sum of recorded actual costs.">
        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Budget</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ $money['budget'] !== null ? number_format($money['budget'], 2) . ' ETB' : 'Not set' }}</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Planned (tasks)</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ number_format($money['planned'], 2) }} ETB</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Spent</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ number_format($money['spent'], 2) }} ETB</dd>
            </div>
            <div>
                <dt class="text-[13px] font-normal text-muted-foreground">Remaining</dt>
                <dd @class(['tabular m-0 mt-1 font-medium', 'text-destructive' => ($money['remaining'] ?? 0) < 0])>
                    {{ $money['remaining'] !== null ? number_format($money['remaining'], 2) . ' ETB' : 'N/A' }}
                </dd>
            </div>
        </dl>
        @if ($money['used_percent'] !== null)
            <x-ui.progress class="mt-5" :value="min($money['used_percent'], 100)" />
            <p class="m-0 mt-2 text-[13px] text-muted-foreground">{{ $money['used_percent'] }}% of the budget spent{{ $money['planned'] > $money['budget'] ? ', and task estimates exceed the budget' : '' }}.</p>
        @endif
    </x-ui.card>

    @include('admin.projects.partials.progress-stats')

    @include('admin.projects.partials.progress-charts')

    @include('admin.projects.partials.timeline')

    {{-- Phases --}}
    <x-ui.card class="mt-6" :title="'Phases (' . $project->phases->count() . ')'" description="Open a phase to manage its tasks." flush>
        @if ($project->phases->isEmpty())
            <x-ui.empty-state icon="layers" title="No phases yet" description="Break the project into phases to start adding tasks.">
                @canvisit(route('admin.projects.phases.create', $project))
                    <x-ui.button :href="route('admin.projects.phases.create', $project)">
                        <i data-lucide="plus" class="size-4"></i>
                        Create first phase
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @else
            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($project->phases as $phase)
                    <div class="flex flex-col overflow-hidden rounded-xl border border-border bg-card">
                        <div class="flex-1 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('admin.phases.show', $phase) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                    {{ $phase->name }}
                                </a>
                                <x-ui.badge :variant="$phase->status_color">{{ $phase->status?->name ?? 'N/A' }}</x-ui.badge>
                            </div>
                            @if ($phase->description)
                                <p class="m-0 mt-1 text-[13px] text-muted-foreground">{{ Str::limit($phase->description, 80) }}</p>
                            @endif
                            <x-ui.progress class="mt-4" :value="$phase->progress_percentage" />
                            <p class="m-0 mt-3 flex items-center gap-3 text-[13px] text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5"><i data-lucide="list-todo" class="size-4"></i>{{ $phase->tasks->count() }} tasks</span>
                                @if ($phase->end_date)
                                    <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="size-4"></i>{{ $phase->end_date->format('M d, Y') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-border bg-muted px-4 py-2">
                            <div class="flex items-center gap-0.5">
                                @canvisit(route('admin.phases.edit', $phase))
                                    <x-ui.icon-button icon="pencil" label="Edit phase" :href="route('admin.phases.edit', $phase)" />
                                @endcanvisit
                                @canvisit(route('admin.phases.destroy', $phase), 'DELETE')
                                    <form action="{{ route('admin.phases.destroy', $phase) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Delete this phase? All tasks will be deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.icon-button type="submit" icon="trash-2" label="Delete phase" tone="destructive" />
                                    </form>
                                @endcanvisit
                            </div>
                            <x-ui.button variant="outline" size="sm" :href="route('admin.phases.show', $phase)">Open</x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    @can('view-project-files')
        @include('admin.projects.partials.files')
    @endcan
@endsection
