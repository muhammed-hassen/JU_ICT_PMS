@extends('layouts.console')

@section('title', 'Edit User')

@section('content_header')
    <x-ui.page-header title="Edit User" :description="'Update the account for ' . $user->name . '.'" />
@stop

@section('content')
    <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <x-ui.card class="max-w-3xl" title="Update User" description="Leave the password empty to keep the current one.">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-ui.label for="name">Name <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="text" name="name" id="name" :value="old('name', $user->name)" :invalid="$errors->has('name')" />
                    @error('name')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="email" name="email" id="email" :value="old('email', $user->email)" :invalid="$errors->has('email')" />
                    @error('email')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="password">Password (leave empty if not changing)</x-ui.label>
                    <x-ui.input type="password" name="password" id="password" autocomplete="new-password" :invalid="$errors->has('password')" />
                    @error('password')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.label for="role_id">Role <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.select name="role_id" id="role_id" :invalid="$errors->has('role_id')">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
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
                        Update User
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.card>
    </form>
@stop
