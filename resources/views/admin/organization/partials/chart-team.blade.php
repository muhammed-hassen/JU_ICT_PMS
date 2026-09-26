{{-- One team in the org chart; includes itself for each sub-team. --}}
<div class="rounded-xl border border-border bg-card p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="m-0 font-semibold text-foreground">{{ $team->name }}</p>
        <span class="text-[13px] text-muted-foreground">{{ $team->members->count() }} {{ Str::plural('member', $team->members->count()) }}</span>
    </div>
    <p class="m-0 mt-1 inline-flex items-center gap-1.5 text-sm">
        <i data-lucide="user-check" class="size-4 text-muted-foreground"></i>
        <span class="text-muted-foreground">Team Leader:</span>
        <span class="font-medium">{{ $team->teamLeader->name ?? 'Not assigned' }}</span>
    </p>
    @php $members = $team->members->where('id', '!=', $team->team_leader_id); @endphp
    @if ($members->isNotEmpty())
        <ul class="m-0 mt-3 flex list-none flex-wrap gap-1.5 p-0">
            @foreach ($members as $member)
                <li class="rounded-md bg-muted px-2 py-1 text-[13px]">{{ $member->name }}</li>
            @endforeach
        </ul>
    @endif

    @if ($depth < 4 && $team->childTeams->isNotEmpty())
        <div class="mt-4 grid gap-3 border-l-2 border-border pl-4">
            @foreach ($team->childTeams as $child)
                @include('admin.organization.partials.chart-team', ['team' => $child->loadMissing(['teamLeader', 'members', 'childTeams']), 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
