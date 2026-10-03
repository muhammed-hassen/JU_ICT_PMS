{{-- Task checklist (subtasks). Editors add and remove items; the assignee can tick them off. --}}
@php
    $subtasks = $task->subtasks;
    $doneCount = $subtasks->where('is_done', true)->count();
    // Mirrors SubtaskController::authorizeTask, so a box is only clickable when the click would succeed.
    $canTick = auth()->user()->can('edit-task')
        || ($task->isAssignedTo(auth()->user())
            && auth()->user()->hasAnyPermission(['edit-own-task', 'complete-task', 'update-task-progress']));
@endphp

<x-ui.card id="subtasks" class="mt-5 scroll-mt-20" title="Subtasks" :description="$subtasks->isEmpty() ? 'Break this task into smaller steps.' : $doneCount . ' of ' . $subtasks->count() . ' done'" flush>
    @if ($subtasks->isNotEmpty())
        <div class="px-5 pt-4">
            <x-ui.progress :value="$subtasks->count() ? ($doneCount / $subtasks->count()) * 100 : 0" />
        </div>
        <ul class="m-0 mt-2 list-none divide-y divide-border p-0">
            @foreach ($subtasks as $subtask)
                <li class="flex items-center gap-3 px-5 py-2.5">
                    @if ($canTick)
                        <form action="{{ route('admin.subtasks.toggle', $subtask) }}" method="POST" class="m-0 flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" aria-pressed="{{ $subtask->is_done ? 'true' : 'false' }}"
                                    aria-label="{{ $subtask->is_done ? 'Mark as not done' : 'Mark as done' }}: {{ $subtask->title }}"
                                    @class(['flex size-5 items-center justify-center rounded border transition-colors duration-200',
                                            'border-success bg-success text-white' => $subtask->is_done,
                                            'border-input bg-card text-transparent hover:border-primary' => ! $subtask->is_done])>
                                <i data-lucide="check" class="size-3.5"></i>
                            </button>
                        </form>
                    @else
                        <span @class(['flex size-5 items-center justify-center rounded border',
                                      'border-success bg-success text-white' => $subtask->is_done,
                                      'border-input text-transparent' => ! $subtask->is_done])>
                            <i data-lucide="check" class="size-3.5"></i>
                        </span>
                    @endif

                    <span @class(['flex-1', 'text-muted-foreground line-through' => $subtask->is_done])>{{ $subtask->title }}</span>

                    @canvisit(route('admin.subtasks.destroy', $subtask), 'DELETE')
                        <form action="{{ route('admin.subtasks.destroy', $subtask) }}" method="POST" class="m-0"
                              onsubmit="return confirm('Remove this checklist item?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.icon-button type="submit" icon="x" label="Remove item" tone="destructive" />
                        </form>
                    @endcanvisit
                </li>
            @endforeach
        </ul>
    @endif

    @canvisit(route('admin.tasks.subtasks.store', $task), 'POST')
        <form action="{{ route('admin.tasks.subtasks.store', $task) }}" method="POST" class="flex gap-2 border-t border-border px-5 py-4">
            @csrf
            <label for="subtask-title" class="sr-only">New checklist item</label>
            <x-ui.input id="subtask-title" name="title" size="sm" placeholder="Add an item" required maxlength="200" :invalid="$errors->has('title')" />
            <x-ui.button type="submit" variant="outline" size="sm">Add</x-ui.button>
        </form>
    @else
        @if ($subtasks->isEmpty())
            <p class="m-0 px-5 py-4 text-sm text-muted-foreground">No checklist items.</p>
        @endif
    @endcanvisit
</x-ui.card>
