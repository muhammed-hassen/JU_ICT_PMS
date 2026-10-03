{{-- "2/5 subtasks" under a task title. Needs withCount(['subtasks', 'subtasks as subtasks_done_count' => ...]) on the query. --}}
@if (($task->subtasks_count ?? 0) > 0)
    <a href="{{ route('admin.tasks.show', $task) }}#subtasks"
       class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-muted-foreground no-underline hover:text-primary hover:no-underline"
       title="Open the task to see its subtasks">
        <i data-lucide="list-checks" class="size-3.5"></i>
        <span class="tabular">{{ $task->subtasks_done_count }}/{{ $task->subtasks_count }}</span> subtasks
    </a>
@endif
