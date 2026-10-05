{{--
    Activity list shared by the activity pages.
    $activities   paginator of ActivityLog
    $showUser     show who did it, linked to their activity page (default true)
    $showDetails  show the stored properties in a details toggle (default true)
    $groupByDate  add Today / Yesterday / date headings (default true)
    $relativeTime show "3 hours ago" instead of the clock time (default false)
--}}
@php
    $showUser = $showUser ?? true;
    $showDetails = $showDetails ?? true;
    $groupByDate = $groupByDate ?? true;
    $relativeTime = $relativeTime ?? false;
    $actionIcons = [
        'created' => 'circle-plus',
        'updated' => 'pencil',
        'deleted' => 'trash-2',
        'restored' => 'rotate-ccw',
        'status_changed' => 'arrow-left-right',
        'assigned' => 'user-plus',
        'reassigned' => 'user-pen',
        'progress_updated' => 'chart-line',
        'reordered' => 'arrow-up-down',
        'completed' => 'circle-check',
        'reopened' => 'folder-open',
    ];
    $iconTones = [
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'danger' => 'bg-destructive/10 text-destructive',
        'info' => 'bg-primary/10 text-primary',
        'primary' => 'bg-primary/10 text-primary',
    ];
    $lastDate = null;
@endphp

<ul class="m-0 list-none p-0">
    @foreach ($activities as $activity)
        @php
            $currentDate = $activity->created_at->format('Y-m-d');
            $dateLabel = null;
            if ($groupByDate && $currentDate !== $lastDate) {
                $dateLabel = $activity->created_at->isToday() ? 'Today'
                    : ($activity->created_at->isYesterday() ? 'Yesterday' : $activity->created_at->format('M d, Y'));
                $lastDate = $currentDate;
            }
            // Link to the thing that changed. There is no page for a
            // single log entry (admin.activities.show never existed).
            $subjectUrl = match (true) {
                $activity->loggable instanceof \App\Models\Project => route('admin.projects.show', $activity->loggable),
                $activity->loggable instanceof \App\Models\Phase => route('admin.phases.show', $activity->loggable),
                $activity->loggable instanceof \App\Models\Task => route('admin.tasks.show', $activity->loggable),
                default => null,
            };
        @endphp

        @if ($dateLabel)
            <li class="border-b border-border bg-muted px-5 py-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">
                {{ $dateLabel }}
            </li>
        @endif

        <li class="flex gap-3 border-b border-border/70 px-5 py-4 last:border-0">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $iconTones[$activity->color] ?? 'bg-muted text-muted-foreground' }}">
                <i data-lucide="{{ $actionIcons[$activity->action] ?? 'circle' }}" class="size-4"></i>
            </span>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    @if ($showUser)
                        @if ($activity->user)
                            <a href="{{ route('admin.activity.user', $activity->user) }}" class="font-semibold text-foreground">{{ $activity->user->name }}</a>
                        @else
                            <span class="font-semibold text-foreground">System</span>
                        @endif
                    @endif
                    <x-ui.badge :variant="$activity->color">{{ $activity->action_label }}</x-ui.badge>
                    <x-ui.badge>{{ $activity->type_label }}</x-ui.badge>
                </div>

                <p class="m-0 mt-1 text-sm text-foreground">{{ $activity->description }}</p>

                @if ($showDetails && $activity->properties)
                    <details class="group mt-2">
                        <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 text-[13px] font-medium text-muted-foreground hover:text-foreground">
                            <i data-lucide="chevron-right" class="size-4 transition-transform group-open:rotate-90"></i>
                            View details
                        </summary>
                        <pre class="m-0 mt-2 overflow-x-auto rounded-lg border border-border bg-muted p-3 text-xs text-foreground">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                @endif
            </div>

            <div class="-mt-1.5 flex shrink-0 items-center gap-1 self-start">
                <time datetime="{{ $activity->created_at->toIso8601String() }}" class="whitespace-nowrap text-xs text-muted-foreground">
                    {{ $relativeTime ? $activity->created_at->diffForHumans() : $activity->created_at->format('h:i A') }}
                </time>
                @if ($subjectUrl)
                    @canvisit($subjectUrl)
                        <x-ui.icon-button icon="eye" :label="'View ' . strtolower($activity->type_label)" :href="$subjectUrl" />
                    @endcanvisit
                @endif
            </div>
        </li>
    @endforeach
</ul>
