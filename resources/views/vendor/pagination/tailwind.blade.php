{{-- Default pagination for ->links(). Overrides Laravel's built-in Tailwind view with the JU styling. --}}
@if ($paginator->hasPages())
    @php
        $item = 'inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2.5 text-[13px] font-medium no-underline transition-colors duration-200 hover:no-underline';
        $idle = 'border-border bg-card text-foreground hover:bg-muted hover:text-foreground';
        $disabled = 'border-border bg-card text-muted-foreground/50 cursor-not-allowed';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3">
        <p class="m-0 text-[13px] text-muted-foreground">
            Showing <span class="tabular font-semibold text-foreground">{{ $paginator->firstItem() }}</span>
            to <span class="tabular font-semibold text-foreground">{{ $paginator->lastItem() }}</span>
            of <span class="tabular font-semibold text-foreground">{{ $paginator->total() }}</span>
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="{{ $item }} {{ $disabled }}" aria-disabled="true" aria-label="Previous page">
                    <i data-lucide="chevron-left" class="size-4"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $item }} {{ $idle }}" aria-label="Previous page">
                    <i data-lucide="chevron-left" class="size-4"></i>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $item }} border-transparent text-muted-foreground" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $item }} tabular border-primary bg-primary text-primary-foreground" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $item }} {{ $idle }} tabular" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $item }} {{ $idle }}" aria-label="Next page">
                    <i data-lucide="chevron-right" class="size-4"></i>
                </a>
            @else
                <span class="{{ $item }} {{ $disabled }}" aria-disabled="true" aria-label="Next page">
                    <i data-lucide="chevron-right" class="size-4"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
