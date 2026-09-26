{{--
    Status chip. Soft tint only; colour is reserved for states that mean something.
    Also accepts the Bootstrap colour names the models still return
    (e.g. Phase::status_color), so views can pass those straight through.
--}}
@props(['variant' => 'neutral'])

@php
    $variant = [
        'info' => 'primary',
        'danger' => 'destructive',
        'secondary' => 'neutral',
        'dark' => 'neutral',
        'light' => 'neutral',
    ][$variant] ?? $variant;
    $variants = [
        'neutral' => 'border-border bg-muted text-muted-foreground',
        'primary' => 'border-primary/20 bg-primary/10 text-ju-blue-700',
        'success' => 'border-success/20 bg-success/10 text-success',
        'warning' => 'border-warning/20 bg-warning/10 text-warning',
        'destructive' => 'border-destructive/20 bg-destructive/10 text-destructive',
    ];
@endphp

<span {{ $attributes->class('inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold leading-4 ' . ($variants[$variant] ?? $variants['neutral'])) }}>{{ $slot }}</span>
