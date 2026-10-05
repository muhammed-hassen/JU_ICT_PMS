@php
    $isEdit        = $teamLeader->exists;
    $selectedTeams = old('team_ids', $isEdit ? $teamLeader->ledTeams->pluck('id')->all() : []);
@endphp

<x-ui.card class="max-w-3xl"
           :title="$isEdit ? 'Edit team leader' : 'Assign team leader'"
           :description="$isEdit ? 'Leave the password empty to keep the current one.' : 'Select an existing user to promote to Team Leader.'">

    <div class="grid gap-5 sm:grid-cols-2">

        {{-- ── CREATE MODE: pick from existing users ── --}}
        @unless ($isEdit)
            <div class="sm:col-span-2">
                <x-ui.label for="user_id">User <span class="text-destructive">*</span></x-ui.label>
                <x-ui.select name="user_id" id="user_id" :invalid="$errors->has('user_id')" required>
                    <option value="">Select a user…</option>
                    @foreach ($eligibleUsers as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} — {{ $u->email }}
                        </option>
                    @endforeach
                </x-ui.select>
                @if ($eligibleUsers->isEmpty())
                    <p class="m-0 mt-1.5 text-[13px] text-muted-foreground">All existing users are already Team Leaders.</p>
                @else
                    <p class="m-0 mt-1.5 text-[13px] text-muted-foreground">Only users who are not yet Team Leaders are listed.</p>
                @endif
                @error('user_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>
        @endunless

        {{-- ── EDIT MODE: update name / email / password ── --}}
        @if ($isEdit)
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
                <x-ui.label for="password">New password</x-ui.label>
                <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" />
                @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.label for="password_confirmation">Confirm new password</x-ui.label>
                <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
            </div>
        @endif

        {{-- ── TEAMS (both modes) ── --}}
        <div class="{{ $isEdit ? 'sm:col-span-2' : 'sm:col-span-2' }}">
            <x-ui.label for="team_ids">Teams led</x-ui.label>
            <select name="team_ids[]" id="team_ids" multiple size="6" aria-describedby="team_ids_help"
                    @class([
                        'block w-full rounded-lg border bg-card px-2 py-1.5 text-sm text-foreground shadow-xs focus:outline-none focus:ring-[3px]',
                        'border-input focus:border-ring focus:ring-ring/15'         => ! $errors->has('team_ids') && ! $errors->has('team_ids.*'),
                        'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('team_ids') || $errors->has('team_ids.*'),
                    ])>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" class="rounded px-2 py-1" @selected(in_array($team->id, $selectedTeams))>
                        {{ $team->name }}
                        @if ($team->teamLeader && $team->teamLeader->id !== $teamLeader->id)
                            — currently led by {{ $team->teamLeader->name }}
                        @endif
                    </option>
                @endforeach
            </select>
            <p id="team_ids_help" class="m-0 mt-1.5 text-[13px] text-muted-foreground">
                Hold Ctrl (Cmd on Mac) to select multiple teams.
                Selecting a team assigns this user as its leader.
            </p>
            @error('team_ids')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            @error('team_ids.*')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

    </div>

    <x-slot:footer>
        <span class="text-muted-foreground">
            {{ $isEdit ? 'Saving updates the user\'s details and team assignments.' : 'The selected user will receive the Team Leader role.' }}
        </span>
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
