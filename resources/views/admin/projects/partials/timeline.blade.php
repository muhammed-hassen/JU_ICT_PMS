<x-ui.card class="mt-6" title="Timeline" :description="count($timelineData) . ' ' . Str::plural('phase', count($timelineData))">
    @if (empty($timelineData))
        <x-ui.empty-state icon="calendar-range" title="No phases yet" description="Phases appear here in order once they are created." class="py-6" />
    @else
        <ol class="relative m-0 list-none p-0">
            @foreach ($timelineData as $phase)
                @php
                    $done = $phase['progress'] >= 100;
                    $started = $phase['progress'] > 0;
                @endphp
                <li class="relative pb-5 pl-9 last:pb-0">
                    @unless ($loop->last)
                        <span class="absolute left-[11px] top-7 bottom-0 w-px bg-border" aria-hidden="true"></span>
                    @endunless
                    <span @class([
                        'absolute left-0 top-0.5 flex size-6 items-center justify-center rounded-full border',
                        'border-success bg-success text-white' => $done,
                        'border-primary bg-card text-primary' => ! $done && $started,
                        'border-border bg-card text-muted-foreground' => ! $started,
                    ])>
                        <i data-lucide="{{ $done ? 'check' : 'circle' }}" class="size-3.5"></i>
                    </span>

                    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.phases.show', $phase['id']) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">{{ $phase['name'] }}</a>
                            <x-ui.badge :variant="$phase['color']">{{ $phase['status'] }}</x-ui.badge>
                        </div>
                        <span class="tabular text-[13px] text-muted-foreground">
                            @if ($phase['start'] && $phase['end'])
                                {{ \Carbon\Carbon::parse($phase['start'])->format('M d') }} to {{ \Carbon\Carbon::parse($phase['end'])->format('M d, Y') }}
                            @else
                                No dates set
                            @endif
                        </span>
                    </div>
                    <x-ui.progress class="mt-2 max-w-md" :value="$phase['progress']" />
                </li>
            @endforeach
        </ol>
    @endif
</x-ui.card>
