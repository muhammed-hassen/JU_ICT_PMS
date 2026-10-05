@extends('layouts.console')

@section('title', $phase->name)

@section('content_header')
    <a href="{{ route('admin.projects.phases.index', $phase->project) }}" class="mb-3 inline-flex items-center gap-1.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-foreground hover:no-underline">
        <i data-lucide="arrow-left" class="size-4"></i>
        Back to Phases
    </a>
    <x-ui.page-header :title="$phase->name" :description="'Phase Details · ' . $phase->project->name">
        @canvisit(route('admin.phases.edit', $phase))
            <x-ui.button variant="outline" :href="route('admin.phases.edit', $phase)">
                <i data-lucide="pencil" class="size-4"></i>
                Edit
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    @php $label = 'text-[13px] font-normal text-muted-foreground'; @endphp

    {{-- Phase info --}}
    <x-ui.card title="Overview">
        <x-slot:actions>
            <x-ui.badge :variant="$phase->status_color">{{ $phase->status?->name ?? 'N/A' }}</x-ui.badge>
        </x-slot:actions>

        <dl class="m-0 grid gap-x-8 gap-y-5 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <dt class="{{ $label }}">Project</dt>
                <dd class="m-0 mt-1">
                    <a href="{{ route('admin.projects.show', $phase->project) }}" class="font-medium">{{ $phase->project->name }}</a>
                </dd>
            </div>
            <div>
                <dt class="{{ $label }}">Sort Order</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ $phase->sort_order }}</dd>
            </div>
            <div>
                <dt class="{{ $label }}">Tasks</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ $phase->tasks->count() }} tasks</dd>
            </div>
            <div>
                <dt class="{{ $label }}">Progress</dt>
                <dd class="m-0 mt-1.5"><x-ui.progress :value="$phase->progress_percentage" /></dd>
            </div>
            <div>
                <dt class="{{ $label }}">Start Date</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ $phase->start_date ? $phase->start_date->format('M d, Y') : 'N/A' }}</dd>
            </div>
            <div>
                <dt class="{{ $label }}">End Date</dt>
                <dd class="tabular m-0 mt-1 font-medium">{{ $phase->end_date ? $phase->end_date->format('M d, Y') : 'N/A' }}</dd>
            </div>
            @if ($phase->start_date && $phase->end_date)
                <div>
                    <dt class="{{ $label }}">Planned Duration</dt>
                    <dd class="tabular m-0 mt-1 font-medium">{{ $phase->planned_duration }} days</dd>
                </div>
            @endif
            <div>
                <dt class="{{ $label }}">Created By</dt>
                <dd class="m-0 mt-1 font-medium">{{ $phase->creator->name ?? 'Unknown' }}</dd>
            </div>
        </dl>

        @if ($phase->description)
            <div class="mt-6">
                <p class="m-0 {{ $label }}">Description</p>
                <p class="m-0 mt-1 max-w-3xl whitespace-pre-line">{{ $phase->description }}</p>
            </div>
        @endif

        <x-slot:footer>
            <span class="text-muted-foreground">
                Created {{ $phase->created_at->diffForHumans() }} · Last updated {{ $phase->updated_at->diffForHumans() }}
            </span>
        </x-slot:footer>
    </x-ui.card>

    {{-- Task stats --}}
    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total Tasks" :value="$phase->task_stats['total'] ?? 0" icon="list-todo" />
        <x-ui.stat-card label="Completed" :value="$phase->task_stats['completed'] ?? 0" icon="circle-check" />
        <x-ui.stat-card label="In Progress" :value="$phase->task_stats['in_progress'] ?? 0" icon="loader" />
        <x-ui.stat-card label="Blocked" :value="$phase->task_stats['blocked'] ?? 0" icon="ban" />
    </div>

    @include('admin.projects.phases.partials.milestones')

    {{-- Tasks --}}
    <x-ui.card class="mt-6" title="Tasks in this Phase" flush>
        @canvisit(route('admin.phases.tasks.create', $phase))
            <x-slot:actions>
                <x-ui.button size="sm" :href="route('admin.phases.tasks.create', $phase)">
                    <i data-lucide="plus" class="size-4"></i>
                    Add Task
                </x-ui.button>
            </x-slot:actions>
        @endcanvisit

        @if ($phase->tasks->isEmpty())
            <x-ui.empty-state icon="list-todo" title="No tasks in this phase yet.">
                @canvisit(route('admin.phases.tasks.create', $phase))
                    <x-ui.button :href="route('admin.phases.tasks.create', $phase)">
                        <i data-lucide="plus" class="size-4"></i>
                        Create First Task
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Task</x-ui.th>
                            <x-ui.th>Priority</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th>Assignee</x-ui.th>
                            <x-ui.th class="min-w-40">Progress</x-ui.th>
                            <x-ui.th>Deadline</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($phase->tasks as $task)
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="min-w-56">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                        {{ $task->title }}
                                    </a>
                                    @if ($task->description)
                                        <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($task->description, 50) }}</p>
                                    @endif
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap">
                                    <x-ui.badge :variant="$task->priority_color">{{ $task->priority->name ?? 'None' }}</x-ui.badge>
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap">
                                    <x-ui.badge :variant="$task->status_color">{{ $task->status->name ?? 'Unknown' }}</x-ui.badge>
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap">
                                    @if ($task->assignee)
                                        {{ $task->assignee->name }}
                                    @else
                                        <span class="text-muted-foreground">Unassigned</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <x-ui.progress :value="$task->progress_percentage" />
                                </x-ui.td>
                                <x-ui.td class="tabular whitespace-nowrap">
                                    @if ($task->deadline)
                                        @if ($task->isOverdue())
                                            <span class="inline-flex items-center gap-1.5 font-medium text-destructive">
                                                <i data-lucide="circle-alert" class="size-4"></i>
                                                {{ $task->deadline->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted-foreground">{{ $task->deadline->format('M d, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted-foreground">No deadline</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.tasks.show', $task))
                                            <x-ui.icon-button icon="eye" label="View task" :href="route('admin.tasks.show', $task)" />
                                        @endcanvisit
                                        @canvisit(route('admin.tasks.edit', $task))
                                            <x-ui.icon-button icon="pencil" label="Edit task" :href="route('admin.tasks.edit', $task)" />
                                        @endcanvisit
                                        @canvisit(route('admin.tasks.destroy', $task), 'DELETE')
                                            <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="m-0"
                                                  onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.icon-button type="submit" icon="trash-2" label="Delete task" tone="destructive" />
                                            </form>
                                        @endcanvisit
                                    </div>
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
@endsection
