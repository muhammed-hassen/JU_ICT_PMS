<link rel="stylesheet" href="{{ asset('css/ju-custom.css') }}">

@php
    $priorityLevel = $task->getPriorityLevelAttribute();
    $badgeColor = $task->getPriorityBadgeColorAttribute();
    $deadlineStatus = $task->getDeadlineStatusAttribute();
    $deadlineColor = $task->getDeadlineColorAttribute();
@endphp

<div class="d-flex flex-wrap align-items-center gap-1">
    <span class="badge badge-{{ $badgeColor }} priority-badge" title="Priority: {{ $priorityLevel }}">
        @if($priorityLevel == 'Critical')
            <i class="fas fa-exclamation-circle"></i>
        @elseif($priorityLevel == 'High')
            <i class="fas fa-arrow-up"></i>
        @elseif($priorityLevel == 'Medium')
            <i class="fas fa-minus"></i>
        @else
            <i class="fas fa-arrow-down"></i>
        @endif
        {{ $priorityLevel }}
    </span>

    @if($task->deadline)
        <span class="badge badge-{{ $deadlineColor }} deadline-badge ml-1" title="Deadline: {{ $task->deadline->format('M d, Y') }}">
            @if($deadlineStatus == 'overdue')
                <i class="fas fa-exclamation-triangle"></i>
            @elseif($deadlineStatus == 'urgent')
                <i class="fas fa-clock"></i>
            @else
                <i class="fas fa-calendar-alt"></i>
            @endif
            {{ $task->getDeadlineBadgeAttribute() }}
        </span>
    @endif

    @if($task->isOverdue())
        <span class="badge badge-danger ml-1">
            <i class="fas fa-exclamation-circle"></i> Overdue
        </span>
    @endif
</div>