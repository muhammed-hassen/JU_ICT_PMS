@extends('layouts.console')

@section('title', 'Team Leaders')

@section('content_header')
    <x-ui.page-header title="Team Leaders" description="The people who lead each team.">
        @canvisit(route('admin.organization.team-leaders.create'))
            <x-ui.button :href="route('admin.organization.team-leaders.create')">
                <i data-lucide="plus" class="size-4"></i>
                Create Team Leader
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Team Leader Directory</h3>
            <x-ui.badge>{{ $teamLeaders->total() }} total</x-ui.badge>
        </div>

        @if ($teamLeaders->isEmpty())
            <x-ui.empty-state icon="users" title="No team leaders found." description="Team leaders you add will be listed here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Email</x-ui.th>
                            <x-ui.th>Leads</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teamLeaders as $teamLeader)
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                                            {{ \Illuminate\Support\Str::of($teamLeader->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                                        </span>
                                        <span class="font-medium">{{ $teamLeader->name }}</span>
                                    </div>
                                </x-ui.td>
                                <x-ui.td class="text-muted-foreground">{{ $teamLeader->email }}</x-ui.td>
                                <x-ui.td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($teamLeader->ledTeams as $team)
                                            <x-ui.badge variant="primary">{{ $team->name }}</x-ui.badge>
                                        @empty
                                            <span class="text-muted-foreground">No teams assigned</span>
                                        @endforelse
                                    </div>
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.organization.team-leaders.show', $teamLeader))
                                            <x-ui.icon-button icon="eye" label="View team leader" :href="route('admin.organization.team-leaders.show', $teamLeader)" />
                                        @endcanvisit
                                        @canvisit(route('admin.organization.team-leaders.edit', $teamLeader))
                                            <x-ui.icon-button icon="pencil" label="Edit team leader" :href="route('admin.organization.team-leaders.edit', $teamLeader)" />
                                        @endcanvisit
                                        @canvisit(route('admin.organization.team-leaders.destroy', $teamLeader), 'DELETE')
                                            <form action="{{ route('admin.organization.team-leaders.destroy', $teamLeader) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this team leader?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.icon-button type="submit" icon="trash-2" label="Delete team leader" tone="destructive" />
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

        @if ($teamLeaders->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $teamLeaders->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
