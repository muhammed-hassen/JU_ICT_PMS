@extends('layouts.console')

@section('title', 'Task Management')

@section('content_header')
    <x-ui.page-header title="Tasks" description="Every task across projects and phases.">
        <x-ui.button variant="outline" :href="route('admin.tasks.my')">
            <i data-lucide="user-check" class="size-4"></i>
            My tasks
        </x-ui.button>
        <x-ui.button variant="outline" :href="route('admin.tasks.kanban')">
            <i data-lucide="square-kanban" class="size-4"></i>
            Board view
        </x-ui.button>
        @can('create-task')
            <x-ui.button :href="route('admin.tasks.create')">
                <i data-lucide="plus" class="size-4"></i>
                New task
            </x-ui.button>
        @endcan
    </x-ui.page-header>
@endsection

@section('content')
    {{-- Filters: one row, every control the same height. --}}
    <form method="GET" action="{{ route('admin.tasks.index') }}" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="relative min-w-56 flex-1">
            <label for="task-search" class="sr-only">Search</label>
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
            <x-ui.input id="task-search" type="text" name="search" class="pl-9" placeholder="Search tasks" :value="request('search')" />
        </div>
        <div class="w-full sm:w-40">
            <label for="filter-status" class="sr-only">Status</label>
            <x-ui.select id="filter-status" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
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
        <div class="w-full sm:w-44">
            <label for="filter-assignee" class="sr-only">Assignee</label>
            <x-ui.select id="filter-assignee" name="assignee">
                <option value="">All assignees</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-36">
            <label for="filter-overdue" class="sr-only">Overdue</label>
            <x-ui.select id="filter-overdue" name="overdue">
                <option value="">All tasks</option>
                <option value="1" {{ request('overdue') == 1 ? 'selected' : '' }}>Overdue only</option>
            </x-ui.select>
        </div>
        <div class="flex gap-2">
            <x-ui.button type="submit" variant="outline">Apply</x-ui.button>
            @if (request()->hasAny(['search', 'status', 'priority', 'assignee', 'overdue']))
                <x-ui.button variant="ghost" :href="route('admin.tasks.index')">Clear</x-ui.button>
            @endif
        </div>
    </form>

    @include('admin.tasks.partials.priority-stats')

    <x-ui.card class="mt-6" flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">All tasks</h3>
            <x-ui.badge>{{ $tasks->total() }} total</x-ui.badge>
        </div>

        @if ($tasks->isEmpty())
            <x-ui.empty-state icon="list-todo" title="No tasks found" description="Try clearing the filters, or add tasks from a project's phases.">
                <x-ui.button variant="outline" :href="route('admin.projects.index')">Go to projects</x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Task</x-ui.th>
                            <x-ui.th>Project / phase</x-ui.th>
                            <x-ui.th>Priority / deadline</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th>Assignee</x-ui.th>
                            <x-ui.th class="min-w-40">Progress</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
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
                                <x-ui.td class="min-w-40">
                                    <span class="block font-medium">{{ $task->phase->project->name ?? 'N/A' }}</span>
                                    <span class="block text-[13px] text-muted-foreground">{{ $task->phase->name ?? 'No phase' }}</span>
                                </x-ui.td>
                                <x-ui.td class="min-w-44">
                                    @include('admin.tasks.partials.priority-badge')
                                </x-ui.td>
                                <x-ui.td>
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
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.icon-button icon="eye" label="View task" :href="route('admin.tasks.show', $task)" />
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

        @if ($tasks->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $tasks->appends(request()->query())->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select[name="status"], select[name="priority"], select[name="assignee"], select[name="overdue"]').forEach(function (input) {
            input.addEventListener('change', function () {
                this.closest('form').submit();
            });
        });
    });
</script>
@endpush
