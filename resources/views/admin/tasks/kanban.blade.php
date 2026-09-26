@extends('layouts.console')

@section('title', 'Task Board')

@section('content_header')
    <x-ui.page-header title="Task board" description="Drag a card to another column to change its status.">
        <x-ui.button variant="outline" :href="route('admin.tasks.index')">
            <i data-lucide="list" class="size-4"></i>
            List view
        </x-ui.button>
        @can('create-task')
            <x-ui.button :href="route('admin.tasks.create')">
                <i data-lucide="plus" class="size-4"></i>
                Add task
            </x-ui.button>
        @endcan
    </x-ui.page-header>
@endsection

@section('content')
    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.tasks.kanban') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-56">
            <label for="filter-project" class="sr-only">Project</label>
            <x-ui.select id="filter-project" name="project">
                <option value="">All projects</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-48">
            <label for="filter-assignee" class="sr-only">Assignee</label>
            <x-ui.select id="filter-assignee" name="assignee">
                <option value="">All assignees</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-40">
            <label for="filter-priority" class="sr-only">Priority</label>
            <x-ui.select id="filter-priority" name="priority">
                <option value="">All priorities</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ request('priority') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="flex gap-2">
            <x-ui.button type="submit" variant="outline">Apply</x-ui.button>
            @if (request()->hasAny(['project', 'assignee', 'priority']))
                <x-ui.button variant="ghost" :href="route('admin.tasks.kanban')">Clear</x-ui.button>
            @endif
        </div>
    </form>

    {{-- Priority Stats --}}
    @php
        $tasks = collect();
        foreach ($boardData as $status => $data) {
            $tasks = $tasks->merge($data['tasks']);
        }
    @endphp
    @include('admin.tasks.partials.priority-stats', ['tasks' => $tasks])

    {{-- Status counts --}}
    <dl class="m-0 mt-6 grid grid-cols-[repeat(auto-fit,minmax(9rem,1fr))] gap-3">
        @foreach ($statusStats as $status => $stats)
            <div class="rounded-xl border border-border bg-card px-4 py-3 shadow-xs">
                <dt class="truncate text-[13px] font-normal text-muted-foreground">{{ $status }}</dt>
                <dd class="m-0 mt-1 flex items-baseline gap-2">
                    <span class="tabular text-2xl font-semibold">{{ $stats['count'] }}</span>
                    <span class="tabular text-[13px] text-muted-foreground">{{ $stats['percentage'] }}%</span>
                </dd>
            </div>
        @endforeach
    </dl>

    {{-- Board --}}
    <div class="-mx-4 mt-6 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8" id="kanban-board">
        <div class="flex min-w-max gap-4">
            @foreach ($boardData as $status => $data)
                <section class="flex w-72 shrink-0 flex-col rounded-xl border border-border bg-muted/60" aria-label="{{ $status }}">
                    <header class="flex items-center justify-between gap-2 px-3 py-2.5">
                        <h3 class="m-0 font-sans text-[13px] font-semibold text-foreground">{{ $status }}</h3>
                        <x-ui.badge>{{ $data['count'] }}</x-ui.badge>
                    </header>
                    <div class="kanban-column flex max-h-[600px] min-h-[300px] flex-col gap-2 overflow-y-auto rounded-b-xl p-2" data-status="{{ $status }}">
                        @forelse ($data['tasks'] as $task)
                            <article class="kanban-item cursor-grab rounded-lg border border-border bg-card p-3 shadow-xs transition-shadow duration-200 hover:shadow-card"
                                     data-task-id="{{ $task->id }}"
                                     draggable="true">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="block font-semibold leading-snug text-foreground no-underline hover:text-primary hover:no-underline">
                                    {{ Str::limit($task->title, 40) }}
                                </a>
                                @include('admin.tasks.partials.subtask-count')
                                <div class="mt-2">
                                    @include('admin.tasks.partials.priority-badge')
                                </div>
                                <div class="mt-2 space-y-0.5 text-xs text-muted-foreground">
                                    <p class="m-0 flex items-center gap-1.5"><i data-lucide="user" class="size-3.5"></i>{{ $task->assignee->name ?? 'Unassigned' }}</p>
                                    <p class="m-0 flex items-center gap-1.5"><i data-lucide="folder-kanban" class="size-3.5"></i>{{ $task->phase->project->name ?? 'N/A' }}</p>
                                </div>
                                @if ($task->progress_percentage > 0)
                                    <x-ui.progress class="mt-2" :value="$task->progress_percentage" :label="false" />
                                @endif
                            </article>
                        @empty
                            <p class="m-0 py-6 text-center text-[13px] text-muted-foreground">No tasks</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection

@push('css')
<style>
    .kanban-item.dragging { opacity: 0.5; cursor: grabbing; }
    .kanban-column.drag-over { background: hsl(210 74% 46% / 0.06); outline: 2px dashed var(--ring); outline-offset: -4px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // DRAG AND DROP
    // ============================================================
    const columns = document.querySelectorAll('.kanban-column');
    const items = document.querySelectorAll('.kanban-item');

    items.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.taskId);
            this.classList.add('dragging');
        });
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        column.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const taskId = e.dataTransfer.getData('text/plain');
            const newStatus = this.dataset.status;
            
            fetch('{{ route("admin.tasks.kanban-reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to move task. ' + (data.error || ''));
                }
            })
            .catch(error => {
                alert('Error moving task');
            });
        });
    });
});
</script>
@endpush