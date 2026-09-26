@php
    $priorityLevel = $task->getPriorityLevelAttribute();
    $deadlineStatus = $task->getDeadlineStatusAttribute();

    // Colour only for the two levels that should change what someone does next.
    $priorityVariant = ['Critical' => 'destructive', 'High' => 'warning'][$priorityLevel] ?? 'neutral';
    $priorityIcon = ['Critical' => 'circle-alert', 'High' => 'arrow-up', 'Medium' => 'minus'][$priorityLevel] ?? 'arrow-down';
    $deadlineVariant = ['overdue' => 'destructive', 'urgent' => 'warning'][$deadlineStatus] ?? 'neutral';
    $deadlineIcon = ['overdue' => 'triangle-alert', 'urgent' => 'clock'][$deadlineStatus] ?? 'calendar';
@endphp

<div class="flex flex-wrap items-center gap-1">
    <x-ui.badge :variant="$priorityVariant" title="Priority: {{ $priorityLevel }}">
        <i data-lucide="{{ $priorityIcon }}" class="size-3"></i>
        {{ $priorityLevel }}
    </x-ui.badge>

    @if ($task->deadline)
        <x-ui.badge :variant="$deadlineVariant" title="Deadline: {{ $task->deadline->format('M d, Y') }}">
            <i data-lucide="{{ $deadlineIcon }}" class="size-3"></i>
            {{ $task->getDeadlineBadgeAttribute() }}
        </x-ui.badge>
    @endif

    @if ($task->isOverdue() && $deadlineStatus !== 'overdue')
        <x-ui.badge variant="destructive">
            <i data-lucide="circle-alert" class="size-3"></i>
            Overdue
        </x-ui.badge>
    @endif
</div>
