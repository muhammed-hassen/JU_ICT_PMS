@php
    $selectedTeams = old('team_ids', $member->exists ? $member->teams->pluck('id')->all() : []);
@endphp

<x-ui.card class="max-w-3xl" title="Member details" :description="$member->exists ? 'Leave the password empty to keep the current one.' : null">
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-ui.label for="name">Name <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="text" name="name" id="name" :value="old('name', $member->name)" :invalid="$errors->has('name')" required />
            @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="email" name="email" id="email" :value="old('email', $member->email)" :invalid="$errors->has('email')" required />
            @error('email')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password">Password @unless($member->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" :required="! $member->exists" />
            @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password_confirmation">Confirm Password @unless($member->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" :required="! $member->exists" />
        </div>
    </div>

    <fieldset class="m-0 mt-5 border-0 p-0">
        <legend class="mb-1.5 text-[13px] font-semibold text-foreground">Teams</legend>
        @if ($teams->isEmpty())
            <p class="m-0 text-sm text-muted-foreground">No teams yet.</p>
        @else
            <label for="team-filter" class="sr-only">Filter teams</label>
            <div class="relative mb-2">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                <x-ui.input id="team-filter" type="search" class="pl-9" placeholder="Filter teams" autocomplete="off" />
            </div>
            <ul id="team-list" class="m-0 grid max-h-64 list-none gap-1 overflow-y-auto rounded-lg border border-border p-2 sm:grid-cols-2">
                @foreach ($teams as $team)
                    <li data-name="{{ strtolower($team->name) }}">
                        <label class="m-0 flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 font-normal hover:bg-muted">
                            <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" class="size-4 accent-[var(--primary)]"
                                   @checked(in_array($team->id, (array) $selectedTeams))>
                            <span class="truncate text-sm font-medium text-foreground">{{ $team->name }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Tick every team this person belongs to.</p>
        @endif
        @error('team_ids')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        @error('team_ids.*')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
    </fieldset>

    <x-slot:footer>
        <div class="flex w-full justify-end gap-2">
            @canvisit(route('admin.organization.members.index'))
                <x-ui.button variant="outline" :href="route('admin.organization.members.index')">Cancel</x-ui.button>
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
        var filter = document.getElementById('team-filter');
        var items = document.querySelectorAll('#team-list li');
        if (!filter) return;
        filter.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        filter.addEventListener('input', function () {
            var q = filter.value.trim().toLowerCase();
            items.forEach(function (li) { li.hidden = q && li.dataset.name.indexOf(q) === -1; });
        });
    })();
</script>
@endpush
