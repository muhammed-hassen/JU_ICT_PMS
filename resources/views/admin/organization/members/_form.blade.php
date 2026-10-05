@php
    $isCreate = ! isset($member) || ! $member->exists;
    $selectedTeams = old('team_ids', $isCreate ? [] : $member->teams->pluck('id')->all());
@endphp

{{-- ============================================================ --}}
{{-- CREATE FLOW — pick an existing user + assign to teams        --}}
{{-- ============================================================ --}}
@if ($isCreate)

<x-ui.card class="max-w-3xl" title="Select user" description="Choose an existing account to add as a team member.">

    {{-- User search / select --}}
    <div>
        <x-ui.label for="user-search">User <span class="text-destructive">*</span></x-ui.label>

        {{-- Visible search input --}}
        <div class="relative mt-1">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
            <x-ui.input
                id="user-search"
                type="search"
                class="pl-9"
                placeholder="Search by name or email…"
                autocomplete="off"
                :invalid="$errors->has('user_id')"
            />
        </div>

        {{-- Hidden real field --}}
        <input type="hidden" name="user_id" id="user-id-input" value="{{ old('user_id') }}" />

        {{-- Dropdown list --}}
        <ul id="user-dropdown"
            class="z-20 mt-1 hidden max-h-60 w-full overflow-y-auto rounded-lg border border-border bg-popover shadow-md">
        </ul>

        @error('user_id')
            <p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>
        @enderror

        {{-- Selected user preview --}}
        <div id="selected-user-preview" class="mt-3 hidden items-center gap-3 rounded-lg border border-border bg-muted/40 px-3 py-2">
            <i data-lucide="user-check" class="size-5 shrink-0 text-primary"></i>
            <div class="min-w-0">
                <p id="selected-user-name" class="m-0 truncate text-sm font-semibold text-foreground"></p>
                <p id="selected-user-email" class="m-0 truncate text-[13px] text-muted-foreground"></p>
            </div>
            <button type="button" id="clear-user"
                    class="ml-auto shrink-0 rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                    aria-label="Clear selection">
                <i data-lucide="x" class="size-4"></i>
            </button>
        </div>
    </div>

    {{-- Team assignment --}}
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
                            <input type="checkbox" name="team_ids[]" value="{{ $team->id }}"
                                   class="size-4 accent-[var(--primary)]"
                                   @checked(in_array($team->id, (array) old('team_ids', [])))>
                            <span class="truncate text-sm font-medium text-foreground">{{ $team->name }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <p class="m-0 mt-1 text-[13px] text-muted-foreground">Tick every team this person should belong to.</p>
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
                <i data-lucide="user-plus" class="size-4"></i>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>

{{-- ============================================================ --}}
{{-- EDIT FLOW — update existing member's name/email/pw/teams     --}}
{{-- ============================================================ --}}
@else

<x-ui.card class="max-w-3xl" title="Member details" description="Leave the password empty to keep the current one.">
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
            <x-ui.label for="password">Password</x-ui.label>
            <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" />
            @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password_confirmation">Confirm Password</x-ui.label>
            <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
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

@endif

@push('js')
<script>
(function () {
    // ── Team filter (used on both create and edit) ──────────────────
    var teamFilter = document.getElementById('team-filter');
    if (teamFilter) {
        var teamItems = document.querySelectorAll('#team-list li');
        teamFilter.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        teamFilter.addEventListener('input', function () {
            var q = teamFilter.value.trim().toLowerCase();
            teamItems.forEach(function (li) { li.hidden = q && li.dataset.name.indexOf(q) === -1; });
        });
    }

    // ── User search autocomplete (create flow only) ─────────────────
    var searchInput  = document.getElementById('user-search');
    var hiddenInput  = document.getElementById('user-id-input');
    var dropdown     = document.getElementById('user-dropdown');
    var preview      = document.getElementById('selected-user-preview');
    var previewName  = document.getElementById('selected-user-name');
    var previewEmail = document.getElementById('selected-user-email');
    var clearBtn     = document.getElementById('clear-user');

    if (!searchInput) return; // edit view — nothing to do

    // All users passed from the controller, serialised into the page.
    var allUsers = @json($existingUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]));

    // Restore a previously selected value (validation failure redirect).
    var restoredId = hiddenInput.value;
    if (restoredId) {
        var restoredUser = allUsers.find(function (u) { return String(u.id) === restoredId; });
        if (restoredUser) showPreview(restoredUser);
    }

    function renderDropdown(users) {
        dropdown.innerHTML = '';
        if (users.length === 0) {
            dropdown.innerHTML = '<li class="px-3 py-2 text-sm text-muted-foreground">No users found.</li>';
        } else {
            users.forEach(function (u) {
                var li = document.createElement('li');
                li.className = 'flex cursor-pointer flex-col gap-0.5 px-3 py-2 hover:bg-muted';
                li.innerHTML =
                    '<span class="text-sm font-medium text-foreground">' + escHtml(u.name) + '</span>' +
                    '<span class="text-[13px] text-muted-foreground">' + escHtml(u.email) + '</span>';
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // keep focus on input long enough to register click
                    selectUser(u);
                });
                dropdown.appendChild(li);
            });
        }
        dropdown.classList.remove('hidden');
    }

    function selectUser(u) {
        hiddenInput.value = u.id;
        searchInput.value = '';
        dropdown.classList.add('hidden');
        showPreview(u);
    }

    function showPreview(u) {
        previewName.textContent  = u.name;
        previewEmail.textContent = u.email;
        preview.classList.remove('hidden');
        preview.classList.add('flex');
    }

    function clearSelection() {
        hiddenInput.value = '';
        searchInput.value = '';
        preview.classList.add('hidden');
        preview.classList.remove('flex');
        dropdown.classList.add('hidden');
        searchInput.focus();
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    searchInput.addEventListener('input', function () {
        var q = searchInput.value.trim().toLowerCase();
        if (q.length < 1) { dropdown.classList.add('hidden'); return; }
        var filtered = allUsers.filter(function (u) {
            return u.name.toLowerCase().indexOf(q) !== -1 || u.email.toLowerCase().indexOf(q) !== -1;
        });
        renderDropdown(filtered.slice(0, 20));
    });

    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length > 0) {
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    searchInput.addEventListener('blur', function () {
        // Slight delay so mousedown on list item fires first.
        setTimeout(function () { dropdown.classList.add('hidden'); }, 150);
    });

    if (clearBtn) clearBtn.addEventListener('click', clearSelection);
})();
</script>
@endpush
