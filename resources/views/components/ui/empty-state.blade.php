{{-- Empty state: glyph, title, one line, and a way out in the default slot. --}}
@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center px-6 py-12 text-center') }}>
    <span class="flex size-16 items-center justify-center rounded-full bg-muted text-muted-foreground">
        <i data-lucide="{{ $icon }}" class="size-7"></i>
    </span>
    <p class="m-0 mt-4 font-semibold text-foreground">{{ $title }}</p>
    @if ($description)
        <p class="m-0 mt-1 max-w-sm text-sm text-muted-foreground">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
