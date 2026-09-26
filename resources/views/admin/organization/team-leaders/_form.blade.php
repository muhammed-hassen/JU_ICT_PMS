@php
    $selectedTeams = old('team_ids', $teamLeader->exists ? $teamLeader->ledTeams->pluck('id')->all() : []);
@endphp

<x-ui.card class="max-w-3xl" title="Team leader details" :description="$teamLeader->exists ? 'Leave the password empty to keep the current one.' : 'Fields marked * are required.'">
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-ui.label for="name">Name <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="text" name="name" id="name" :value="old('name', $teamLeader->name)" :invalid="$errors->has('name')" required />
            @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="email" name="email" id="email" :value="old('email', $teamLeader->email)" :invalid="$errors->has('email')" required />
            @error('email')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password">Password @unless($teamLeader->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" :required="! $teamLeader->exists" />
            @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password_confirmation">Confirm Password @unless($teamLeader->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" :required="! $teamLeader->exists" />
        </div>

        <div class="sm:col-span-2">
            <x-ui.label for="team_ids">Teams Led</x-ui.label>
            <select name="team_ids[]" id="team_ids" multiple size="8" aria-describedby="team_ids_help"
                    @class([
                        'block w-full rounded-lg border bg-card px-2 py-1.5 text-sm text-foreground shadow-xs focus:outline-none focus:ring-[3px]',
                        'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('team_ids') && ! $errors->has('team_ids.*'),
                        'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('team_ids') || $errors->has('team_ids.*'),
                    ])>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" class="rounded px-2 py-1" @selected(in_array($team->id, $selectedTeams))>
                        {{ $team->name }}
                        @if ($team->teamLeader && $team->teamLeader->id !== $teamLeader->id)
                            - currently led by {{ $team->teamLeader->name }}
                        @endif
                    </option>
                @endforeach
            </select>
            <p id="team_ids_help" class="m-0 mt-1.5 text-[13px] text-muted-foreground">Selecting a team here assigns this user as its leader. Hold Ctrl (Cmd on Mac) to pick more than one.</p>
            @error('team_ids')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            @error('team_ids.*')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
    </div>

    <x-slot:footer>
        <span class="text-muted-foreground">Saving gives this user the Team Leader role.</span>
        <div class="flex items-center gap-2">
            @canvisit(route('admin.organization.team-leaders.index'))
                <x-ui.button variant="outline" :href="route('admin.organization.team-leaders.index')">Cancel</x-ui.button>
            @endcanvisit
            <x-ui.button type="submit">
                <i data-lucide="save" class="size-4"></i>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
