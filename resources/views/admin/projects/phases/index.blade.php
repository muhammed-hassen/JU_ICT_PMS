@extends('layouts.console')

@section('title', "Phases - {$project->name}")

@section('content_header')
    <x-ui.page-header :title="'Project Phases: ' . $project->name" description="Phases in this project, in order.">
        @canvisit(route('admin.projects.show', $project))
            <x-ui.button variant="outline" :href="route('admin.projects.show', $project)">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to Project
            </x-ui.button>
        @endcanvisit
        @canvisit(route('admin.projects.phases.create', $project))
            <x-ui.button :href="route('admin.projects.phases.create', $project)">
                <i data-lucide="plus" class="size-4"></i>
                Add Phase
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Phases ({{ $phases->count() }})</h3>
            <x-ui.badge>Total: {{ $phases->count() }}</x-ui.badge>
        </div>

        @if ($phases->isEmpty())
            <x-ui.empty-state icon="layers" title="No phases created yet." description="Break the project into phases to start adding tasks.">
                @canvisit(route('admin.projects.phases.create', $project))
                    <x-ui.button :href="route('admin.projects.phases.create', $project)">
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
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th>Tasks</x-ui.th>
                            <x-ui.th class="min-w-40">Progress</x-ui.th>
                            <x-ui.th>Dates</x-ui.th>
                            <x-ui.th>Created</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody id="sortable-phases">
                        @foreach ($phases as $phase)
                            <tr data-id="{{ $phase->id }}" class="border-b border-border/70 bg-card last:border-0 hover:bg-background">
                                <x-ui.td>
                                    <span class="tabular inline-flex size-7 items-center justify-center rounded-md bg-muted text-[13px] font-semibold text-muted-foreground">{{ $phase->sort_order }}</span>
                                </x-ui.td>
                                <x-ui.td class="min-w-56">
                                    <a href="{{ route('admin.phases.show', $phase) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                        {{ $phase->name }}
                                    </a>
                                    @if ($phase->description)
                                        <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($phase->description, 50) }}</p>
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
                                <x-ui.td class="whitespace-nowrap text-[13px] text-muted-foreground">{{ $phase->created_at->diffForHumans() }}</x-ui.td>
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
                                                  onsubmit="return confirm('Are you sure you want to delete this phase? All tasks will be deleted.')">
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

        @can('reorder-phases')
            <x-slot:footer>
                <span class="inline-flex items-center gap-1.5 text-muted-foreground">
                    <i data-lucide="grip-vertical" class="size-4"></i>
                    Drag and drop phases to reorder them
                </span>
            </x-slot:footer>
        @endcan
    </x-ui.card>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-phases');
        if (el) {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'opacity-50',
                chosenClass: 'bg-muted',
                onEnd: function() {
                    const order = [];
                    document.querySelectorAll('#sortable-phases tr').forEach(row => {
                        order.push(row.dataset.id);
                    });

                    fetch('{{ route("admin.projects.phases.reorder", $project) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ phases: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                }
            });
        }
    });
</script>
@endpush
