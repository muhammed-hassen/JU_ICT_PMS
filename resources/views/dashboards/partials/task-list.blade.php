{{-- Compact task rows for dashboards: title, project, who, and the deadline. --}}
@if ($tasks->isEmpty())
    <p class="m-0 px-5 py-8 text-center text-sm text-muted-foreground">{{ $empty ?? 'Nothing here.' }}</p>
@else
    <ul class="m-0 list-none divide-y divide-border p-0">
        @foreach ($tasks as $task)
            @php $overdue = $task->isOverdue(); @endphp
            <li class="flex items-center gap-3 px-5 py-3">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('admin.tasks.show', $task) }}" class="block truncate font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $task->title }}</a>
                    <p class="m-0 truncate text-[13px] text-muted-foreground">
                        {{ $task->phase->project->name ?? 'No project' }}@if (($showAssignee ?? true) && $task->assignee) · {{ $task->assignee->name }}@endif
                    </p>
                </div>
                @if ($task->deadline)
                    <x-ui.badge :variant="$overdue ? 'destructive' : 'neutral'">
                        {{ $overdue ? $task->deadline->diffForHumans(null, true) . ' late' : 'Due ' . $task->deadline->format('M j') }}
                    </x-ui.badge>
                @endif
            </li>
        @endforeach
    </ul>
@endif
