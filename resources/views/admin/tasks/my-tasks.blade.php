@extends('layouts.console')
@section('title', 'My Tasks')

@section('content_header')
    <x-ui.page-header title="My tasks" :description="$stats['total'] . ' ' . Str::plural('task', $stats['total']) . ' assigned to you.'">
        <x-ui.button variant="outline" :href="route('admin.tasks.kanban')">
            <i data-lucide="square-kanban" class="size-4"></i>
            Board view
        </x-ui.button>
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Not started" :value="$stats['not_started']" icon="circle-dashed" />
        <x-ui.stat-card label="In progress" :value="$stats['in_progress']" icon="loader" />
        <x-ui.stat-card label="Completed" :value="$stats['completed']" icon="circle-check" />
        <x-ui.stat-card label="Overdue" :value="$stats['overdue']" icon="triangle-alert" />
    </div>

    <x-ui.card class="mt-6" flush>
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 mr-auto font-sans text-[15px] font-semibold">Assigned to you</h3>
            <div class="relative w-full sm:w-56">
                <label for="my-search" class="sr-only">Search</label>
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="my-search" type="text" name="search" class="pl-9" placeholder="Search tasks" :value="request('search')" />
            </div>
            <label for="my-status" class="sr-only">Status</label>
            <x-ui.select id="my-status" name="status" class="w-full sm:w-40">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </x-ui.select>
            <label for="my-priority" class="sr-only">Priority</label>
            <x-ui.select id="my-priority" name="priority" class="w-full sm:w-40">
                <option value="">All priorities</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit" variant="outline">Apply</x-ui.button>
            @if (request()->hasAny(['search', 'status', 'priority']))
                <x-ui.button variant="ghost" :href="route('admin.tasks.my')">Clear</x-ui.button>
            @endif
        </form>

        @if ($tasks->isEmpty())
            <x-ui.empty-state icon="circle-check" title="Nothing assigned to you" description="Tasks assigned to you will show up here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Task</x-ui.th>
                            <x-ui.th>Project</x-ui.th>
                            <x-ui.th>Priority</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th class="min-w-40">Progress</x-ui.th>
                            <x-ui.th>Deadline</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            @php
                                $priorityVariant = ['Critical' => 'destructive', 'High' => 'warning'][$task->priority->name ?? ''] ?? 'neutral';
                            @endphp
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="min-w-56">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                        {{ Str::limit($task->title, 40) }}
                                    </a>
                                    @include('admin.tasks.partials.subtask-count')
                                    @if ($task->description)
                                        <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($task->description, 40) }}</p>
                                    @endif
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap">{{ $task->phase->project->name ?? 'N/A' }}</x-ui.td>
                                <x-ui.td><x-ui.badge :variant="$priorityVariant">{{ $task->priority->name ?? 'None' }}</x-ui.badge></x-ui.td>
                                <x-ui.td><x-ui.badge :variant="$task->status_color">{{ $task->status->name ?? 'Unknown' }}</x-ui.badge></x-ui.td>
                                <x-ui.td><x-ui.progress :value="$task->progress_percentage" /></x-ui.td>
                                <x-ui.td class="whitespace-nowrap">
                                    @if ($task->deadline)
                                        @if ($task->isOverdue())
                                            <span class="tabular block font-medium text-destructive">{{ $task->deadline->format('M d, Y') }}</span>
                                            <span class="block text-xs text-destructive">{{ $task->days_overdue }} days overdue</span>
                                        @else
                                            <span class="tabular block">{{ $task->deadline->format('M d, Y') }}</span>
                                            @if ($task->days_remaining)
                                                <span class="block text-xs text-muted-foreground">{{ $task->days_remaining }} days left</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted-foreground">No deadline</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-1">
                                        @canvisit(route('admin.tasks.update-status', $task), 'PATCH')
                                            <form action="{{ route('admin.tasks.update-status', $task) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('PATCH')
                                                <label for="status-{{ $task->id }}" class="sr-only">Change status</label>
                                                <x-ui.select id="status-{{ $task->id }}" name="task_status_id" size="sm" class="w-36" onchange="this.closest('form').submit()">
                                                    @foreach ($task->statusOptionsFor(auth()->user()) as $status)
                                                        <option value="{{ $status->id }}" {{ $task->task_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                                    @endforeach
                                                </x-ui.select>
                                            </form>
                                        @endcanvisit
                                        <x-ui.icon-button icon="eye" label="View task" :href="route('admin.tasks.show', $task)" />
                                        @canvisit(route('admin.tasks.edit', $task))
                                            <x-ui.icon-button icon="pencil" label="Edit task" :href="route('admin.tasks.edit', $task)" />
                                        @endcanvisit
                                    </div>
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($tasks->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $tasks->appends(request()->query())->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
