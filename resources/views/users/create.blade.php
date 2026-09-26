@extends('layouts.console')

@section('title', 'Create User')

@section('content_header')
    <x-ui.page-header title="Create New User" description="Add an account and choose its role." />
@stop

@section('content')
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <x-ui.card class="max-w-3xl" title="User Information">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-ui.label for="name">Name <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="text" name="name" id="name" :value="old('name')" :invalid="$errors->has('name')" />
                    @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="email" name="email" id="email" :value="old('email')" :invalid="$errors->has('email')" />
                    @error('email')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="password">Password <span class="text-destructive">*</span></x-ui.label>
                    <div class="relative">
                        <x-ui.input type="password" name="password" id="password" class="pr-11" autocomplete="new-password" :invalid="$errors->has('password')" required />
                        <button type="button" id="toggle-password" aria-label="Show password" aria-controls="password" aria-pressed="false"
                                class="absolute right-1 top-1/2 inline-flex size-8 -translate-y-1/2 items-center justify-center rounded-md border-0 bg-transparent text-muted-foreground hover:bg-muted hover:text-foreground">
                            <span data-eye="show"><i data-lucide="eye" class="size-4"></i></span>
                            <span data-eye="hide" hidden><i data-lucide="eye-off" class="size-4"></i></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>
                    @else
                        <p class="m-0 mt-1 text-[13px] text-muted-foreground">At least 8 characters, with letters and numbers.</p>
                    @enderror
                </div>
                <div>
                    <x-ui.label for="role_id">Role <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.select name="role_id" id="role_id" :invalid="$errors->has('role_id')">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', isset($user) ? $user->roles->first()?->id : null) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    @error('role_id')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
            </div>

            <x-slot:footer>
                <div class="flex w-full justify-end gap-2">
                    @canvisit(route('users.index'))
                        <x-ui.button variant="outline" :href="route('users.index')">Cancel</x-ui.button>
                    @endcanvisit
                    <x-ui.button type="submit">
                        <i data-lucide="save" class="size-4"></i>
                        Save User
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </form>
@stop

@push('js')
<script>
    (function () {
        var button = document.getElementById('toggle-password');
        var input = document.getElementById('password');
        if (!button || !input) return;
        button.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.querySelector('[data-eye="show"]').hidden = show;
            button.querySelector('[data-eye="hide"]').hidden = !show;
        });
    })();
</script>
@endpush
