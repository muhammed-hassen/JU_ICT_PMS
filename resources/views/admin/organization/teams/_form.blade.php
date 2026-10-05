@php
    $selectedMembers = old('member_ids', $team->exists ? $team->members->pluck('id')->all() : []);
@endphp

<x-ui.card>
    <div class="grid gap-5 lg:grid-cols-3">
        <div class="flex flex-col gap-5 lg:col-span-2">
            <div>
                <x-ui.label for="name">Team Name <span class="text-destructive">*</span></x-ui.label>
                <x-ui.input type="text" name="name" id="name" :value="old('name', $team->name)" :invalid="$errors->has('name')" required />
                @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-ui.label for="description">Description</x-ui.label>
                <textarea name="description" id="description" rows="4"
                          @class([
                              'block w-full rounded-lg border bg-card px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-[3px]',
                              'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'),
                              'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description'),
                          ])>{{ old('description', $team->description) }}</textarea>
                @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col gap-5">
            <div>
                <x-ui.label for="team_leader_id">Team Leader</x-ui.label>
                <x-ui.select name="team_leader_id" id="team_leader_id" :invalid="$errors->has('team_leader_id')">
                    <option value="">Unassigned</option>
                    @foreach ($leaders as $leader)
                        <option value="{{ $leader->id }}" @selected(old('team_leader_id', $team->team_leader_id) == $leader->id)>
                            {{ $leader->name }} ({{ $leader->email }})
                        </option>
                    @endforeach
                </x-ui.select>
                @error('team_leader_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-ui.label for="parent_team_id">Parent Team</x-ui.label>
                <x-ui.select name="parent_team_id" id="parent_team_id" :invalid="$errors->has('parent_team_id')">
                    <option value="">Top level</option>
                    @foreach ($parentTeams as $parentTeam)
                        <option value="{{ $parentTeam->id }}" @selected(old('parent_team_id', $team->parent_team_id) == $parentTeam->id)>
                            {{ $parentTeam->name }}
                        </option>
                    @endforeach
                </x-ui.select>
                @error('parent_team_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <fieldset class="m-0 mt-5 border-0 p-0">
        <legend class="mb-1.5 text-[13px] font-semibold text-foreground">Team Members</legend>
        @if ($members->isEmpty())
            <p class="m-0 text-sm text-muted-foreground">No people to add yet.</p>
        @else
            <label for="member-filter" class="sr-only">Filter people</label>
            <div class="relative mb-2">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="member-filter" type="search" class="pl-9" placeholder="Filter by name or email" autocomplete="off" />
            </div>
            <ul id="member-list" class="m-0 grid max-h-72 list-none gap-1 overflow-y-auto rounded-lg border border-border p-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($members as $member)
                    <li data-name="{{ strtolower($member->name . ' ' . $member->email) }}">
                        <label class="m-0 flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 font-normal hover:bg-muted">
                            <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="size-4 shrink-0 accent-[var(--primary)]"
                                   @checked(in_array($member->id, (array) $selectedMembers))>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-foreground">{{ $member->name }}</span>
                                <span class="block truncate text-xs text-muted-foreground">{{ $member->email }}</span>
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Tick everyone who belongs to this team.</p>
        @endif
        @error('member_ids')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        @error('member_ids.*')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
    </fieldset>

    <x-slot:footer>
        <div class="flex w-full justify-end gap-2">
            @canvisit(route('admin.organization.teams.index'))
                <x-ui.button variant="outline" :href="route('admin.organization.teams.index')">Cancel</x-ui.button>
            @endcanvisit
            <x-ui.button type="submit">
                <i data-lucide="save" class="size-4"></i>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>

@push('js')
<script>
    (function () {
        var filter = document.getElementById('member-filter');
        var items = document.querySelectorAll('#member-list li');
        if (!filter) return;
        filter.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        filter.addEventListener('input', function () {
            var q = filter.value.trim().toLowerCase();
            items.forEach(function (li) { li.hidden = q && li.dataset.name.indexOf(q) === -1; });
        });
    })();
</script>
@endpush
