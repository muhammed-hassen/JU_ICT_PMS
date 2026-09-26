@php
    $selectedPermissions = array_map('intval', (array) old('permissions', $selectedPermissions ?? []));
    $textareaClass = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px] ';
@endphp

<div class="grid gap-6">
    <x-ui.card title="Role details">
        <div class="grid gap-5">
            <div>
                <x-ui.label for="name">Role name</x-ui.label>
                <x-ui.input id="name" name="name" :value="old('name', $role->name)" placeholder="System Administrator" required :invalid="$errors->has('name')" />
                @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-ui.label for="description">Description</x-ui.label>
                <textarea id="description" name="description" rows="3" placeholder="Short summary of what this role is allowed to do"
                          @class([$textareaClass, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description')])>{{ old('description', $role->description) }}</textarea>
                @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Permissions" description="Tick what this role is allowed to do. Use Select all to grant a whole module.">
        @if ($permissionGroups->isEmpty())
            <x-ui.empty-state icon="key-round" title="No permissions found" description="Seed permissions first." />
        @else
            <div class="grid items-start gap-4 md:grid-cols-2">
                @foreach ($permissionGroups as $module => $permissions)
                    @php
                        $moduleLabel = config('rbac.modules')[$module] ?? \Illuminate\Support\Str::headline($module);
                        $moduleKey = \Illuminate\Support\Str::slug($module ?: 'none');
                    @endphp
                    <fieldset class="m-0 min-w-0 overflow-hidden rounded-xl border border-border p-0" data-module-group>
                        <legend class="sr-only">{{ $moduleLabel }}</legend>
                        <div class="flex items-center justify-between gap-3 border-b border-border bg-muted px-4 py-2.5">
                            <p class="m-0 text-sm font-semibold text-foreground" aria-hidden="true">{{ $moduleLabel }}</p>
                            <label for="select-all-{{ $moduleKey }}" class="m-0 flex cursor-pointer items-center gap-2 text-[13px] font-medium text-muted-foreground">
                                <input type="checkbox" id="select-all-{{ $moduleKey }}" class="size-4 rounded-sm accent-[var(--primary)]" data-select-all>
                                Select all
                            </label>
                        </div>
                        <ul class="m-0 grid list-none gap-0.5 p-2">
                            @foreach ($permissions as $permission)
                                <li>
                                    <label for="permission_{{ $permission->id }}" class="m-0 flex cursor-pointer items-start gap-2.5 rounded-md px-2 py-1.5 font-normal hover:bg-muted">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                               class="mt-0.5 size-4 shrink-0 rounded-sm accent-[var(--primary)]" data-permission
                                               @checked(in_array($permission->id, $selectedPermissions))>
                                        <span class="min-w-0">
                                            <span class="block font-mono text-[13px] font-medium text-foreground">{{ $permission->name }}</span>
                                            <span class="block text-xs text-muted-foreground">{{ $permission->description ?: 'No description' }}</span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </fieldset>
                @endforeach
            </div>
        @endif

        @error('permissions')<p class="m-0 mt-3 text-[13px] text-destructive">{{ $message }}</p>@enderror
        @error('permissions.*')<p class="m-0 mt-3 text-[13px] text-destructive">{{ $message }}</p>@enderror

        <x-slot:footer>
            <span class="text-muted-foreground"><span class="tabular" id="permission-count">{{ count($selectedPermissions) }}</span> selected</span>
            <div class="flex items-center gap-2">
                @canvisit(route('admin.roles.index'))
                    <x-ui.button variant="outline" :href="route('admin.roles.index')">Cancel</x-ui.button>
                @endcanvisit
                <x-ui.button type="submit">
                    <i data-lucide="check" class="size-4"></i>
                    {{ $submitLabel }}
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.card>
</div>

@push('js')
<script>
    (function () {
        var counter = document.getElementById('permission-count');
        var groups = document.querySelectorAll('[data-module-group]');

        function refresh() {
            groups.forEach(function (group) {
                var boxes = group.querySelectorAll('[data-permission]');
                var ticked = group.querySelectorAll('[data-permission]:checked').length;
                var all = group.querySelector('[data-select-all]');
                all.checked = boxes.length > 0 && ticked === boxes.length;
                all.indeterminate = ticked > 0 && ticked < boxes.length;
            });
            if (counter) counter.textContent = document.querySelectorAll('[data-permission]:checked').length;
        }

        groups.forEach(function (group) {
            group.querySelector('[data-select-all]').addEventListener('change', function (e) {
                group.querySelectorAll('[data-permission]').forEach(function (box) { box.checked = e.target.checked; });
                refresh();
            });
            group.querySelectorAll('[data-permission]').forEach(function (box) {
                box.addEventListener('change', refresh);
            });
        });

        refresh();
    })();
</script>
@endpush
