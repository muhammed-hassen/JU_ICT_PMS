{{-- Square ghost button holding one lucide icon. Always pass a label for screen readers. --}}
@props(['icon', 'label', 'href' => null, 'type' => 'button', 'tone' => 'default'])

@php
    $classes = 'inline-flex size-8 items-center justify-center rounded-lg border-0 bg-transparent no-underline transition-colors duration-200 hover:no-underline '
        . ($tone === 'destructive' ? 'text-muted-foreground hover:bg-destructive/10 hover:text-destructive' : 'text-muted-foreground hover:bg-muted hover:text-foreground');
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}><i data-lucide="{{ $icon }}" class="size-4"></i></a>
@else
    <button type="{{ $type }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}><i data-lucide="{{ $icon }}" class="size-4"></i></button>
@endif
