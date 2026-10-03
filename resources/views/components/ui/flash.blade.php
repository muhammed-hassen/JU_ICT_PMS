{{-- Session flash messages (success, status, error, warning). --}}
@php
    $messages = array_filter([
        'success' => session('success') ?? session('status'),
        'warning' => session('warning'),
        'error' => session('error'),
    ]);
    $styles = [
        'success' => ['border-success/25 bg-success/5 text-success', 'circle-check'],
        'warning' => ['border-warning/25 bg-warning/5 text-warning', 'triangle-alert'],
        'error' => ['border-destructive/25 bg-destructive/5 text-destructive', 'circle-alert'],
    ];
@endphp

@foreach ($messages as $type => $message)
    <div {{ $attributes->class('mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm ' . $styles[$type][0]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
        <i data-lucide="{{ $styles[$type][1] }}" class="mt-0.5 size-4 shrink-0"></i>
        <span class="flex-1">{{ $message }}</span>
    </div>
@endforeach
