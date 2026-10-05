@php
    $phaseProgress = $progressStats['total_phases'] > 0
        ? ($progressStats['completed_phases'] / $progressStats['total_phases']) * 100
        : 0;
    $taskProgress = $progressStats['total_tasks'] > 0
        ? ($progressStats['completed_tasks'] / $progressStats['total_tasks']) * 100
        : 0;
@endphp

<x-ui.card class="mt-6" title="Progress">
    <x-slot:actions>
        <x-ui.badge variant="primary">{{ number_format($progressStats['overall_progress'], 1) }}% complete</x-ui.badge>
    </x-slot:actions>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-4">
            <div>
                <div class="mb-1.5 flex items-center justify-between text-[13px]">
                    <span class="font-medium">Overall</span>
                    <span class="tabular text-muted-foreground">{{ number_format($progressStats['overall_progress'], 1) }}%</span>
                </div>
                <x-ui.progress :value="$progressStats['overall_progress']" :label="false" />
            </div>
            <div>
                <div class="mb-1.5 flex items-center justify-between text-[13px]">
                    <span class="font-medium">Phases</span>
                    <span class="tabular text-muted-foreground">{{ $progressStats['completed_phases'] }} of {{ $progressStats['total_phases'] }}</span>
                </div>
                <x-ui.progress :value="$phaseProgress" :label="false" />
            </div>
            <div>
                <div class="mb-1.5 flex items-center justify-between text-[13px]">
                    <span class="font-medium">Tasks</span>
                    <span class="tabular text-muted-foreground">{{ $progressStats['completed_tasks'] }} of {{ $progressStats['total_tasks'] }}</span>
                </div>
                <x-ui.progress :value="$taskProgress" :label="false" />
            </div>
        </div>

        @php
            $counts = [
                ['Phases done', $progressStats['completed_phases']],
                ['Phases active', $progressStats['active_phases']],
                ['Phases not started', $progressStats['not_started_phases']],
                ['Tasks done', $progressStats['completed_tasks']],
                ['Tasks in progress', $progressStats['in_progress_tasks']],
                ['Tasks not started', $progressStats['not_started_tasks']],
            ];
        @endphp
        <dl class="m-0 grid grid-cols-3 gap-px overflow-hidden rounded-lg border border-border bg-border">
            @foreach ($counts as [$label, $count])
                <div class="bg-card px-4 py-3">
                    <dt class="text-xs font-normal text-muted-foreground">{{ $label }}</dt>
                    <dd class="tabular m-0 mt-1 text-xl font-semibold">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</x-ui.card>
