{{-- shadcn/ui Button, as a Blade component. Renders <a> when given an href. --}}
@props(['variant' => 'default', 'size' => 'default', 'href' => null, 'type' => 'button'])

@php
    $variants = [
        'default' => 'border-0 bg-primary text-primary-foreground shadow-xs hover:bg-ju-blue-700 hover:text-primary-foreground',
        'secondary' => 'border-0 bg-secondary text-secondary-foreground hover:bg-border',
        'outline' => 'border border-input bg-card text-foreground shadow-xs hover:bg-muted hover:text-foreground',
        'ghost' => 'border-0 text-muted-foreground hover:bg-muted hover:text-foreground',
        'destructive' => 'border border-destructive/20 bg-destructive/10 text-destructive hover:bg-destructive hover:text-white',
        'link' => 'border-0 text-primary underline-offset-4 hover:underline',
    ];
    $sizes = [
        'default' => 'h-10 px-4 text-sm',
        'sm' => 'h-8 px-3 text-[13px]',
        'lg' => 'h-12 px-5 text-[15px]',
        'icon' => 'size-9',
    ];
    $classes = 'inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg font-medium no-underline transition-colors duration-200 hover:no-underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:pointer-events-none disabled:opacity-50 '
        . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
