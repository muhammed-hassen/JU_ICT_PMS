@extends('layouts.console')

@section('title', 'All Phases')

@section('content_header')
    <x-ui.page-header title="All Phases" description="Manage all project phases.">
        @canvisit(route('admin.projects.index'))
            <x-ui.button variant="outline" :href="route('admin.projects.index')">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to Projects
            </x-ui.button>
        @endcanvisit
        @canvisit(route('admin.phases.create'))
            <x-ui.button :href="route('admin.phases.create')">
                <i data-lucide="plus" class="size-4"></i>
                Add Phase
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">All phases</h3>
            <x-ui.badge>{{ $phases->total() }} total</x-ui.badge>
        </div>

        @if ($phases->isEmpty())
            <x-ui.empty-state icon="layers" title="No phases found" description="Phases you can see will show up here.">
                @canvisit(route('admin.phases.create'))
                    <x-ui.button :href="route('admin.phases.create')">
                        <i data-lucide="plus" class="size-4"></i>
                        Create First Phase
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th class="w-12">#</x-ui.th>
                            <x-ui.th>Phase</x-ui.th>
                            <x-ui.th>Project</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th>Tasks</x-ui.th>
                            <x-ui.th class="min-w-40">Progress</x-ui.th>
                            <x-ui.th>Dates</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($phases as $phase)
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="tabular text-muted-foreground">{{ $loop->iteration }}</x-ui.td>
                                <x-ui.td class="min-w-56">
                                    <a href="{{ route('admin.phases.show', $phase) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                        {{ $phase->name }}
                                    </a>
                                    @if ($phase->description)
                                        <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($phase->description, 50) }}</p>
                                    @endif
                                </x-ui.td>
                                <x-ui.td class="min-w-40">
                                    @if ($phase->project)
                                        <a href="{{ route('admin.projects.show', $phase->project) }}" class="font-medium text-foreground no-underline hover:text-primary hover:no-underline">
                                            {{ $phase->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted-foreground">N/A</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap">
                                    <x-ui.badge :variant="$phase->status_color">{{ $phase->status?->name ?? 'N/A' }}</x-ui.badge>
                                </x-ui.td>
                                <x-ui.td class="tabular">{{ $phase->tasks->count() }}</x-ui.td>
                                <x-ui.td>
                                    <x-ui.progress :value="$phase->progress_percentage" />
                                </x-ui.td>
                                <x-ui.td class="tabular whitespace-nowrap text-[13px]">
                                    @if ($phase->start_date)
                                        <span class="block"><span class="text-muted-foreground">Start:</span> {{ $phase->start_date->format('M d, Y') }}</span>
                                    @endif
                                    @if ($phase->end_date)
                                        <span class="block"><span class="text-muted-foreground">End:</span> {{ $phase->end_date->format('M d, Y') }}</span>
                                    @endif
                                    @if (! $phase->start_date && ! $phase->end_date)
                                        <span class="text-muted-foreground">No dates</span>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.phases.show', $phase))
                                            <x-ui.icon-button icon="eye" label="View phase" :href="route('admin.phases.show', $phase)" />
                                        @endcanvisit
                                        @canvisit(route('admin.phases.edit', $phase))
                                            <x-ui.icon-button icon="pencil" label="Edit phase" :href="route('admin.phases.edit', $phase)" />
                                        @endcanvisit
                                        @canvisit(route('admin.phases.destroy', $phase), 'DELETE')
                                            <form action="{{ route('admin.phases.destroy', $phase) }}" method="POST" class="m-0"
                                                  onsubmit="return confirm('Are you sure you want to delete this phase?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.icon-button type="submit" icon="trash-2" label="Delete phase" tone="destructive" />
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

        @if (method_exists($phases, 'hasPages') && $phases->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $phases->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
