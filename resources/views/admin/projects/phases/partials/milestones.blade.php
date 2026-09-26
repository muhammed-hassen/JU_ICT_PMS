{{-- Phase milestones: a deadline and the deliverable due by then. Phase editors manage them. --}}
@php
    $milestones = $phase->milestones;
    $reached = $milestones->whereNotNull('completed_at')->count();
    $canManage = auth()->user()->can('edit-phase');
@endphp

<x-ui.card id="milestones" class="mt-6 scroll-mt-20" title="Milestones and deliverables"
           :description="$milestones->isEmpty() ? 'Set checkpoints for this phase: what is delivered, and by when.' : $reached . ' of ' . $milestones->count() . ' reached'" flush>
    @if ($milestones->isNotEmpty())
        <ul class="m-0 list-none divide-y divide-border p-0">
            @foreach ($milestones as $milestone)
                <li class="flex items-center gap-3 px-5 py-3">
                    @if ($canManage)
                        <form action="{{ route('admin.milestones.toggle', $milestone) }}" method="POST" class="m-0 flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" aria-pressed="{{ $milestone->completed_at ? 'true' : 'false' }}"
                                    aria-label="{{ $milestone->completed_at ? 'Mark as not reached' : 'Mark as reached' }}: {{ $milestone->title }}"
                                    @class(['flex size-5 items-center justify-center rounded border transition-colors duration-200',
                                            'border-success bg-success text-white' => $milestone->completed_at,
                                            'border-input bg-card text-transparent hover:border-primary' => ! $milestone->completed_at])>
                                <i data-lucide="check" class="size-3.5"></i>
                            </button>
                        </form>
                    @else
                        <span @class(['flex size-5 items-center justify-center rounded border',
                                      'border-success bg-success text-white' => $milestone->completed_at,
                                      'border-input text-transparent' => ! $milestone->completed_at])>
                            <i data-lucide="check" class="size-3.5"></i>
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <p @class(['m-0 font-medium', 'text-muted-foreground line-through' => $milestone->completed_at])>{{ $milestone->title }}</p>
                        @if ($milestone->deliverable)
                            <p class="m-0 text-[13px] text-muted-foreground">Deliverable: {{ $milestone->deliverable }}</p>
                        @endif
                    </div>

                    @if ($milestone->due_date)
                        <span @class(['tabular whitespace-nowrap text-[13px]',
                                      'font-medium text-destructive' => $milestone->isOverdue(),
                                      'text-muted-foreground' => ! $milestone->isOverdue()])>
                            {{ $milestone->isOverdue() ? 'Overdue · ' : 'Due ' }}{{ $milestone->due_date->format('M d, Y') }}
                        </span>
                    @endif

                    @if ($canManage)
                        <form action="{{ route('admin.milestones.destroy', $milestone) }}" method="POST" class="m-0"
                              onsubmit="return confirm('Remove this milestone?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.icon-button type="submit" icon="x" label="Remove milestone" tone="destructive" />
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($canManage)
        <form action="{{ route('admin.phases.milestones.store', $phase) }}" method="POST" class="grid gap-2 border-t border-border px-5 py-4 sm:grid-cols-[1fr_1fr_10rem_auto]">
            @csrf
            <label for="milestone-title" class="sr-only">Milestone</label>
            <x-ui.input id="milestone-title" name="title" size="sm" placeholder="Milestone, e.g. Servers ready" required maxlength="200" />
            <label for="milestone-deliverable" class="sr-only">Deliverable</label>
            <x-ui.input id="milestone-deliverable" name="deliverable" size="sm" placeholder="Deliverable, e.g. Config document" maxlength="255" />
            <label for="milestone-due" class="sr-only">Due date</label>
            <x-ui.input type="date" id="milestone-due" name="due_date" size="sm" />
            <x-ui.button type="submit" variant="outline" size="sm">Add</x-ui.button>
        </form>
    @elseif ($milestones->isEmpty())
        <p class="m-0 px-5 py-4 text-sm text-muted-foreground">No milestones set.</p>
    @endif
</x-ui.card>
