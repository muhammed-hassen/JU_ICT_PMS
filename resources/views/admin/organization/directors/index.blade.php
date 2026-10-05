@extends('layouts.console')

@section('title', 'Directors')

@section('content_header')
    <x-ui.page-header title="Directors" description="ICT leadership.">
        @canvisit(route('admin.organization.directors.create'))
            <x-ui.button :href="route('admin.organization.directors.create')">
                <i data-lucide="plus" class="size-4"></i>
                Create Director
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Director Directory</h3>
            <x-ui.badge>{{ $directors->total() }} total</x-ui.badge>
        </div>

        @if ($directors->isEmpty())
            <x-ui.empty-state icon="user-round" title="No directors found." description="Directors you add will be listed here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Email</x-ui.th>
                            <x-ui.th>Led Teams</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($directors as $director)
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                                            {{ \Illuminate\Support\Str::of($director->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                        </span>
                                        <span class="font-medium">{{ $director->name }}</span>
                                    </div>
                                </x-ui.td>
                                <x-ui.td class="text-muted-foreground">{{ $director->email }}</x-ui.td>
                                <x-ui.td class="tabular">{{ $director->led_teams_count }}</x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.organization.directors.show', $director))
                                            <x-ui.icon-button icon="eye" label="View director" :href="route('admin.organization.directors.show', $director)" />
                                        @endcanvisit
                                        {{-- Same rule as the routes (manage-directors), so whoever can edit sees the buttons. --}}
                                        @canvisit(route('admin.organization.directors.edit', $director))
                                            <x-ui.icon-button icon="pencil" label="Edit director" :href="route('admin.organization.directors.edit', $director)" />
                                        @endcanvisit
                                        @canvisit(route('admin.organization.directors.destroy', $director), 'DELETE')
                                            <form action="{{ route('admin.organization.directors.destroy', $director) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this director?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.icon-button type="submit" icon="trash-2" label="Delete director" tone="destructive" />
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

        @if ($directors->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $directors->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
