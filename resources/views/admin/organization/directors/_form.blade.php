<x-ui.card class="max-w-3xl" title="Director details" :description="$director->exists ? 'Leave the password empty to keep the current one.' : 'Fields marked * are required.'">
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-ui.label for="name">Name <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="text" name="name" id="name" :value="old('name', $director->name)" :invalid="$errors->has('name')" required />
            @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="email" name="email" id="email" :value="old('email', $director->email)" :invalid="$errors->has('email')" required />
            @error('email')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password">Password @unless($director->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" :required="! $director->exists" />
            @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-ui.label for="password_confirmation">Confirm Password @unless($director->exists)<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" :required="! $director->exists" />
        </div>
    </div>

    <x-slot:footer>
        <span class="text-muted-foreground">Saving gives this user the ICT Director role.</span>
        <div class="flex items-center gap-2">
            @canvisit(route('admin.organization.directors.index'))
                <x-ui.button variant="outline" :href="route('admin.organization.directors.index')">Cancel</x-ui.button>
            @endcanvisit
            <x-ui.button type="submit">
                <i data-lucide="save" class="size-4"></i>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
