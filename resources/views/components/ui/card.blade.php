{{-- shadcn/ui Card. Optional title, description and actions slots; body is the default slot. --}}
@props(['title' => null, 'description' => null, 'flush' => false])

<div {{ $attributes->class('rounded-xl border border-border bg-card text-card-foreground shadow-xs') }}>
    @if ($title || isset($actions))
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-border px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="m-0 font-sans text-[15px] font-semibold text-foreground">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="m-0 mt-0.5 text-[13px] text-muted-foreground">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['p-5' => ! $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="flex items-center justify-between gap-3 rounded-b-xl border-t border-border bg-muted px-5 py-3 text-[13px]">
            {{ $footer }}
        </div>
    @endisset
</div>
