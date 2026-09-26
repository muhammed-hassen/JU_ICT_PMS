{{-- Thin progress bar. Green only when done. --}}
@props(['value' => 0, 'label' => true])

@php $value = max(0, min(100, (float) $value)); @endphp

<div {{ $attributes->class('flex items-center gap-3') }}>
    <div class="h-1.5 min-w-16 flex-1 overflow-hidden rounded-full bg-muted" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
        <div @class(['h-full rounded-full', 'bg-success' => $value >= 100, 'bg-primary' => $value < 100]) style="width: {{ $value }}%"></div>
    </div>
    @if ($label)
        <span class="tabular w-10 shrink-0 text-right text-[13px] text-muted-foreground">{{ round($value) }}%</span>
    @endif
</div>
