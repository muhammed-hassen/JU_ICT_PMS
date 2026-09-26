{{--
    Stat card, per the Trust Connect console pattern: label over value, one navy
    icon badge. Every card in a row is the same tone; the glyph tells them apart.
--}}
@props(['label', 'value', 'icon' => 'circle', 'hint' => null, 'href' => null, 'linkText' => 'View all'])

<div {{ $attributes->class('flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-xs') }}>
    <div class="flex items-start justify-between gap-4 p-5">
        <div class="min-w-0">
            <p class="m-0 text-[13px] text-muted-foreground">{{ $label }}</p>
            <p class="tabular m-0 mt-1 text-3xl font-semibold leading-none text-foreground">{{ $value }}</p>
            @if ($hint)
                <p class="m-0 mt-2 text-xs text-muted-foreground">{{ $hint }}</p>
            @endif
        </div>
        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-ju-navy text-white">
            <i data-lucide="{{ $icon }}" class="size-5"></i>
        </span>
    </div>
    @if ($href)
        <a href="{{ $href }}" class="mt-auto flex items-center justify-between border-t border-border bg-muted px-5 py-2.5 text-[13px] font-medium text-muted-foreground no-underline hover:text-primary hover:no-underline">
            {{ $linkText }}
            <i data-lucide="arrow-right" class="size-4"></i>
        </a>
    @endif
</div>
