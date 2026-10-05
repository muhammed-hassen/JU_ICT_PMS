{{-- Topbar: menu toggle on small screens, greeting, date, user menu with sign out. --}}
@php
    $user = auth()->user();
    // Full name: seeded accounts are named "Team Member 1", so the first word alone read "Welcome back, Team".
    $displayName = $user?->name;
    $initials = $user
        ? \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')
        : '';
    $roleName = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;
@endphp

<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-border bg-card/95 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button type="button" data-sidebar-toggle class="inline-flex size-9 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground lg:hidden" aria-label="Open navigation">
        <i data-lucide="menu" class="size-5"></i>
    </button>

    @if ($user)
        <p class="m-0 truncate text-[15px] font-medium text-foreground">
            Welcome back, {{ $displayName }}
        </p>
    @endif

    <div class="ml-auto flex items-center gap-2">
        <span class="hidden items-center gap-2 rounded-lg border border-border px-3 py-1.5 text-[13px] text-muted-foreground md:inline-flex">
            <i data-lucide="calendar" class="size-4"></i>
            <span class="tabular">{{ now()->format('D, M j, Y') }}</span>
        </span>

        @if ($user)
            @php
                $unreadCount = $user->unreadNotifications()->count();
                $latestNotifications = $user->notifications()->take(6)->get();
            @endphp
            <details class="relative" data-menu>
                <summary class="relative flex size-9 cursor-pointer list-none items-center justify-center rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground [&::-webkit-details-marker]:hidden"
                         aria-label="Notifications{{ $unreadCount ? ', ' . $unreadCount . ' unread' : '' }}">
                    <i data-lucide="bell" class="size-5"></i>
                    @if ($unreadCount)
                        <span class="tabular absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-semibold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </summary>

                <div class="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-lg border border-border bg-popover shadow-raised">
                    <div class="flex items-center justify-between border-b border-border px-4 py-2.5">
                        <p class="m-0 text-sm font-semibold text-foreground">Notifications</p>
                        @if ($unreadCount)
                            <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                                @csrf
                                <button type="submit" class="border-0 bg-transparent p-0 text-xs font-medium text-primary hover:underline">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    @forelse ($latestNotifications as $note)
                        <a href="{{ route('notifications.open', $note->id) }}"
                           @class(['flex gap-3 border-b border-border/70 px-4 py-3 no-underline last:border-0 hover:bg-accent hover:no-underline', 'bg-primary/5' => ! $note->read_at])>
                            <i data-lucide="{{ $note->data['icon'] ?? 'bell' }}" class="mt-0.5 size-4 shrink-0 text-muted-foreground"></i>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-medium text-foreground">{{ $note->data['title'] ?? 'Notification' }}</span>
                                <span class="block text-xs text-muted-foreground">{{ $note->data['body'] ?? '' }} · {{ $note->created_at->diffForHumans() }}</span>
                            </span>
                            @unless ($note->read_at)
                                <span class="mt-1.5 size-2 shrink-0 rounded-full bg-primary" aria-label="Unread"></span>
                            @endunless
                        </a>
                    @empty
                        <p class="m-0 px-4 py-6 text-center text-sm text-muted-foreground">You're all caught up.</p>
                    @endforelse
                    <a href="{{ route('notifications.index') }}" class="block border-t border-border px-4 py-2.5 text-center text-[13px] font-medium text-primary no-underline hover:bg-accent hover:no-underline">See all notifications</a>
                </div>
            </details>

            <details class="relative" data-menu>
                <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-lg py-1 pl-1 pr-2 hover:bg-muted [&::-webkit-details-marker]:hidden">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary/10 text-[12px] font-semibold text-ju-blue-700">{{ $initials }}</span>
                    <span class="hidden flex-col leading-tight sm:flex">
                        <span class="text-[13px] font-semibold text-foreground">{{ $user->name }}</span>
                        @if ($roleName)
                            <span class="text-[11px] text-muted-foreground">{{ \Illuminate\Support\Str::headline($roleName) }}</span>
                        @endif
                    </span>
                    <i data-lucide="chevron-down" class="size-4 text-muted-foreground"></i>
                </summary>

                <div class="absolute right-0 top-full z-50 mt-2 w-56 rounded-lg border border-border bg-popover p-1 shadow-raised">
                    <div class="px-3 py-2">
                        <p class="m-0 truncate text-[13px] font-semibold text-foreground">{{ $user->name }}</p>
                        <p class="m-0 truncate text-xs text-muted-foreground">{{ $user->email }}</p>
                    </div>
                    <div class="my-1 h-px bg-border"></div>
                    <a href="{{ route('admin.tasks.my') }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-foreground no-underline hover:bg-accent hover:text-foreground hover:no-underline">
                        <i data-lucide="list-todo" class="size-4 text-muted-foreground"></i>
                        My tasks
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-md border-0 bg-transparent px-3 py-2 text-left text-sm text-foreground hover:bg-accent">
                            <i data-lucide="log-out" class="size-4 text-muted-foreground"></i>
                            Sign out
                        </button>
                    </form>
                </div>
            </details>
        @endif
    </div>
</header>
