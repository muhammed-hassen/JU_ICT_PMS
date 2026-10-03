@php
    // Convert to collection if it's a paginator
    if ($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $tasksCollection = $tasks->getCollection();
    } elseif ($tasks instanceof \Illuminate\Database\Eloquent\Collection) {
        $tasksCollection = $tasks;
    } else {
        $tasksCollection = collect($tasks);
    }

    $priorityStats = [
        'Critical' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Critical'; })->count(),
        'High' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'High'; })->count(),
        'Medium' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Medium'; })->count(),
        'Low' => $tasksCollection->filter(function($t) { return $t->priority && $t->priority->name == 'Low'; })->count(),
    ];
    $total = array_sum($priorityStats);

    $deadlineStats = [
        'overdue' => $tasksCollection->filter(function($t) { return $t->isOverdue(); })->count(),
        'due_today' => $tasksCollection->filter(function($t) {
            return $t->deadline && $t->deadline->isToday() && !$t->isOverdue();
        })->count(),
        'due_soon' => $tasksCollection->filter(function($t) {
            return $t->deadline && now()->diffInDays($t->deadline, false) <= 3 && !$t->isOverdue();
        })->count(),
        'on_track' => $tasksCollection->filter(function($t) {
            return $t->deadline && now()->diffInDays($t->deadline, false) > 3 && !$t->isOverdue();
        })->count(),
        'no_deadline' => $tasksCollection->whereNull('deadline')->count(),
    ];

    // Get top 5 urgent tasks
    $urgentTasks = $tasksCollection->filter(function($t) {
        return $t->deadline && !$t->isOverdue() && now()->diffInDays($t->deadline, false) <= 3;
    })->sortBy('deadline')->take(5);

    // Critical and High carry colour; Medium and Low stay quiet.
    $prioritySegments = [
        'Critical' => 'bg-destructive',
        'High' => 'bg-warning',
        'Medium' => 'bg-primary',
        'Low' => 'bg-ju-shield',
    ];
    $deadlineRows = [
        ['Overdue', $deadlineStats['overdue'], 'text-destructive'],
        ['Due today', $deadlineStats['due_today'], 'text-warning'],
        ['Due soon', $deadlineStats['due_soon'], 'text-foreground'],
        ['On track', $deadlineStats['on_track'], 'text-foreground'],
        ['No deadline', $deadlineStats['no_deadline'], 'text-muted-foreground'],
    ];
@endphp

<div class="grid gap-5 lg:grid-cols-2">
    {{-- Priority distribution --}}
    <x-ui.card title="Priority" :description="$total . ' tasks on this page'">
        <div class="flex h-2.5 overflow-hidden rounded-full bg-muted">
            @foreach ($priorityStats as $level => $count)
                @php $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0; @endphp
                @if ($percentage > 0)
                    <div class="{{ $prioritySegments[$level] }}" style="width: {{ $percentage }}%" title="{{ $level }}: {{ $count }} tasks ({{ $percentage }}%)"></div>
                @endif
            @endforeach
        </div>
        <dl class="m-0 mt-4 grid grid-cols-2 gap-x-6 gap-y-2 sm:grid-cols-4">
            @foreach ($priorityStats as $level => $count)
                <div class="flex items-center justify-between gap-2 sm:block">
                    <dt class="flex items-center gap-2 text-[13px] font-normal text-muted-foreground">
                        <span class="size-2 rounded-full {{ $prioritySegments[$level] }}"></span>
                        {{ $level }}
                    </dt>
                    <dd class="tabular m-0 font-semibold sm:mt-0.5">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </x-ui.card>

    {{-- Deadlines --}}
    <x-ui.card title="Deadlines" description="For the tasks on this page.">
        <dl class="m-0 grid grid-cols-3 gap-x-6 gap-y-3 sm:grid-cols-5">
            @foreach ($deadlineRows as [$label, $count, $tone])
                <div>
                    <dt class="text-[13px] font-normal text-muted-foreground">{{ $label }}</dt>
                    <dd class="tabular m-0 mt-0.5 text-xl font-semibold {{ $count > 0 ? $tone : 'text-foreground' }}">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </x-ui.card>

    {{-- Urgent tasks --}}
    @if ($urgentTasks->count() > 0)
        <x-ui.card class="lg:col-span-2" flush>
            <div class="flex items-center gap-2 border-b border-border px-5 py-4">
                <i data-lucide="clock" class="size-4 text-warning"></i>
                <h3 class="m-0 font-sans text-[15px] font-semibold">Due in the next 3 days</h3>
                <x-ui.badge variant="warning">{{ $urgentTasks->count() }}</x-ui.badge>
            </div>
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($urgentTasks as $urgentTask)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                        <a href="{{ route('admin.tasks.show', $urgentTask) }}" class="font-medium text-foreground no-underline hover:text-primary hover:no-underline">
                            {{ $urgentTask->title }}
                        </a>
                        <span class="flex items-center gap-1.5">
                            <x-ui.badge :variant="$urgentTask->priority_color">{{ $urgentTask->priority->name ?? 'None' }}</x-ui.badge>
                            <x-ui.badge variant="warning">
                                <i data-lucide="calendar" class="size-3"></i>
                                {{ $urgentTask->days_remaining }} days left
                            </x-ui.badge>
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
