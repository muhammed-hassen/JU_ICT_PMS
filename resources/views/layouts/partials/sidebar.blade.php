{{--
    Sidebar navigation.

    The menu and each item's 'can' permission live in config/navigation.php.
    This partial only decides how it looks.
--}}
@php
    $icon = fn ($item) => $item['icon'] ?? 'circle';

    // Visible only when the item's 'can' passes AND the route behind the link
    // would let the user in, so the menu never offers a page that ends in a 403.
    $allowed = fn ($item) => (! isset($item['can']) || Gate::any((array) $item['can']))
        && (! isset($item['url']) || \App\Support\RouteAccess::allows(url($item['url'])));

    $currentPath = trim(request()->path(), '/');
    $currentFull = trim(request()->getRequestUri(), '/');

    $matchesExactly = fn ($item) => isset($item['url']) && trim($item['url'], '/') === $currentFull;
    $matchesPattern = function ($item) use ($currentPath) {
        $patterns = $item['active'] ?? (isset($item['url']) ? [strtok($item['url'], '?')] : []);
        foreach ((array) $patterns as $pattern) {
            if (request()->is(trim($pattern, '/'))) {
                return true;
            }
        }
        return false;
    };

    // Build the visible tree, marking one active child per group. An exact URL
    // match wins over a wildcard, so "Board View" is not lit up alongside "All
    // Tasks" just because both patterns cover admin/tasks*.
    $sections = [];
    $current = ['header' => null, 'items' => []];
    foreach (config('navigation.menu', []) as $item) {
        if (! is_array($item) || isset($item['type'])) {
            continue;
        }
        if (isset($item['header'])) {
            $sections[] = $current;
            $current = ['header' => $item['header'], 'items' => []];
            continue;
        }
        if (! $allowed($item)) {
            continue;
        }
        // Live unread count on the Messages link.
        if (($item['url'] ?? null) === 'messages' && auth()->check()) {
            $unreadMessages = auth()->user()->unreadConversationCount();
            if ($unreadMessages) {
                $item['label'] = $unreadMessages;
            }
        }
        if (isset($item['submenu'])) {
            $children = array_values(array_filter($item['submenu'], $allowed));
            if (! $children) {
                continue;
            }
            $activeIndex = null;
            foreach ($children as $i => $child) {
                if ($matchesExactly($child)) { $activeIndex = $i; break; }
            }
            if ($activeIndex === null) {
                foreach ($children as $i => $child) {
                    if ($matchesPattern($child)) { $activeIndex = $i; break; }
                }
            }
            foreach ($children as $i => &$child) {
                $child['is_active'] = $i === $activeIndex;
            }
            unset($child);
            $item['submenu'] = $children;
            $item['is_active'] = $activeIndex !== null;
        } else {
            $item['is_active'] = $matchesExactly($item) || $matchesPattern($item);
        }
        $current['items'][] = $item;
    }
    $sections[] = $current;
    $sections = array_filter($sections, fn ($s) => count($s['items']) > 0);

    $linkBase = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium no-underline transition-colors duration-200 hover:no-underline';
    $linkIdle = 'text-muted-foreground hover:bg-muted hover:text-foreground';
    $linkActive = 'bg-primary/8 text-ju-blue-700 hover:bg-primary/10 hover:text-ju-blue-700';
@endphp

<aside class="shell-sidebar fixed inset-y-0 left-0 z-40 flex w-[264px] flex-col border-r border-border bg-card" aria-label="Main navigation">
    {{-- Brand. Same height as the topbar so the two line up. --}}
    <a href="{{ route('home') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-border px-5 no-underline hover:no-underline">
        <img src="{{ asset('images/ju-logo.jpg') }}" alt="Jimma University crest" class="h-10 w-auto">
        <span class="flex flex-col leading-tight">
            <span class="text-[15px] font-bold tracking-tight text-ju-navy">JU ICT PMS</span>
            <span class="text-[11px] font-medium text-muted-foreground">Jimma University</span>
        </span>
    </a>

    <nav class="shell-scroll flex-1 overflow-y-auto px-3 pb-6">
        @foreach ($sections as $section)
            @if ($section['header'])
                <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-muted-foreground/80">
                    {{ \Illuminate\Support\Str::title(strtolower($section['header'])) }}
                </p>
            @else
                <div class="mt-4"></div>
            @endif

            <ul class="m-0 flex list-none flex-col gap-0.5 p-0">
                @foreach ($section['items'] as $item)
                    <li>
                        @if (isset($item['submenu']))
                            <details class="group" @if ($item['is_active']) open @endif>
                                <summary @class([$linkBase, 'cursor-pointer list-none select-none [&::-webkit-details-marker]:hidden', $item['is_active'] ? 'text-foreground' : $linkIdle])>
                                    <i data-lucide="{{ $icon($item) }}" class="size-[18px]"></i>
                                    <span class="flex-1">{{ $item['text'] }}</span>
                                    <i data-lucide="chevron-right" class="size-4 text-muted-foreground transition-transform duration-200 group-open:rotate-90"></i>
                                </summary>
                                <ul class="mb-0 ml-[21px] mt-0.5 flex list-none flex-col gap-0.5 border-l border-border py-0.5 pl-0">
                                    @foreach ($item['submenu'] as $child)
                                        <li class="-ml-px">
                                            <a href="{{ url($child['url'] ?? '#') }}"
                                               @if ($child['is_active']) aria-current="page" @endif
                                               @class(['flex items-center gap-2 border-l py-1.5 pl-4 pr-3 text-[13px] no-underline transition-colors duration-200 hover:no-underline',
                                                       'border-primary font-semibold text-ju-blue-700' => $child['is_active'],
                                                       'border-transparent text-muted-foreground hover:border-input hover:text-foreground' => ! $child['is_active']])>
                                                <span class="flex-1">{{ $child['text'] }}</span>
                                                @isset($child['label'])
                                                    <x-ui.badge>{{ $child['label'] }}</x-ui.badge>
                                                @endisset
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <a href="{{ url($item['url'] ?? '#') }}"
                               @if ($item['is_active']) aria-current="page" @endif
                               @class([$linkBase, $item['is_active'] ? $linkActive : $linkIdle])>
                                <i data-lucide="{{ $icon($item) }}" class="size-[18px]"></i>
                                <span class="flex-1">{{ $item['text'] }}</span>
                                @isset($item['label'])
                                    <x-ui.badge>{{ $item['label'] }}</x-ui.badge>
                                @endisset
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    {{-- One contextual widget in the footer, per the shell rules: who is signed in. --}}
    @auth
        <div class="shrink-0 border-t border-border p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-1.5">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted text-[13px] font-semibold text-ju-navy">
                    {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                </span>
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-[13px] font-semibold text-foreground">{{ auth()->user()->name }}</span>
                    <span class="block truncate text-[11px] text-muted-foreground">{{ auth()->user()->email }}</span>
                </span>
            </div>
        </div>
    @endauth
</aside>
