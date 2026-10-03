@php
    $textareaClass = 'block w-full rounded-lg border bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px] ';
@endphp

<x-ui.card title="Permission details">
    <div class="grid gap-5">
        <div>
            <x-ui.label for="name">Permission name</x-ui.label>
            <x-ui.input id="name" name="name" class="font-mono" :value="old('name', $permission->name)" placeholder="view-all-projects" required
                        aria-describedby="name-help" :invalid="$errors->has('name')" />
            <p id="name-help" class="m-0 mt-1 text-[13px] text-muted-foreground">Use lowercase words joined by hyphens, for example <code class="rounded bg-muted px-1 py-0.5 text-[12px] text-foreground">view-all-projects</code>.</p>
            @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-ui.label for="module">Module</x-ui.label>
            <x-ui.input id="module" name="module" list="rbac-module-options" :value="old('module', $permission->module)" placeholder="project" required
                        :invalid="$errors->has('module')" />
            <datalist id="rbac-module-options">
                @foreach ($modules as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </datalist>
            @error('module')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-ui.label for="description">Description</x-ui.label>
            <textarea id="description" name="description" rows="3" placeholder="Explain what this permission allows"
                      @class([$textareaClass, 'border-input focus:border-ring focus:ring-ring/15' => ! $errors->has('description'), 'border-destructive focus:border-destructive focus:ring-destructive/15' => $errors->has('description')])>{{ old('description', $permission->description) }}</textarea>
            @error('description')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>

        @if ($permission->exists)
            <div>
                <p class="m-0 mb-1.5 text-[13px] font-semibold text-foreground">Assigned roles</p>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($permission->roles as $role)
                        <x-ui.badge>{{ $role->name }}</x-ui.badge>
                    @empty
                        <span class="text-sm text-muted-foreground">Not assigned to any role yet.</span>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="ml-auto flex items-center gap-2">
            @canvisit(route('admin.permissions.index'))
                <x-ui.button variant="outline" :href="route('admin.permissions.index')">Cancel</x-ui.button>
            @endcanvisit
            <x-ui.button type="submit">
                <i data-lucide="check" class="size-4"></i>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
