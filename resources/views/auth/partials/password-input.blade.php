{{--
    Password field with a show/hide eye, as on the reference sign-in page.
    Usage: @include('auth.partials.password-input', ['id' => 'password', 'name' => 'password', 'autocomplete' => 'current-password'])
--}}
@php
    $name = $name ?? 'password';
    $id = $id ?? $name;
@endphp
<div class="relative">
    <x-ui.input size="lg" :id="$id" type="password" :name="$name" :invalid="$errors->has($name)"
                class="pr-11" :autocomplete="$autocomplete ?? 'current-password'" :autofocus="$autofocus ?? false" required />
    <button type="button" data-password-toggle="{{ $id }}" aria-label="Show password" aria-pressed="false"
            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center border-0 bg-transparent text-muted-foreground hover:text-foreground">
        <svg data-eye-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
        <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden size-5" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
    </button>
</div>
@error($name)
    <p class="m-0 mt-1.5 text-[13px] text-destructive" role="alert">{{ $message }}</p>
@enderror
