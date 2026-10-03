{{-- Page title, one-line description, and at most one primary plus a couple of secondary actions. --}}
@props(['title', 'description' => null])

<div {{ $attributes->class('flex flex-wrap items-end justify-between gap-4') }}>
    <div class="min-w-0">
        <h1 class="m-0 text-2xl font-semibold tracking-tight text-foreground">{{ $title }}</h1>
        @if ($description)
            <p class="m-0 mt-1 text-sm text-muted-foreground">{{ $description }}</p>
        @endif
    </div>
    @if (trim($slot))
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
