{{-- Conversation list shown on the left of every messages page. --}}
<div class="flex min-h-0 flex-col border-border lg:border-r">
    <div class="flex items-center justify-between gap-2 border-b border-border px-4 py-3">
        <h2 class="m-0 font-sans text-[15px] font-semibold">Conversations</h2>
        @can('create-conversation')
            <x-ui.button size="sm" :href="route('messages.create')">
                <i data-lucide="square-pen" class="size-4"></i>
                New
            </x-ui.button>
        @endcan
    </div>

    @if ($conversations->isEmpty())
        <p class="m-0 px-4 py-8 text-center text-sm text-muted-foreground">No conversations yet.</p>
    @else
        <ul class="m-0 min-h-0 flex-1 list-none divide-y divide-border overflow-y-auto p-0">
            @foreach ($conversations as $c)
                @php
                    $isActive = $active && $active->id === $c->id;
                    $last = $c->latestMessage;
                @endphp
                <li>
                    <a href="{{ route('messages.show', $c) }}" @if ($isActive) aria-current="page" @endif
                       @class(['flex gap-3 px-4 py-3 no-underline transition-colors duration-200 hover:no-underline',
                               'bg-primary/8' => $isActive,
                               'hover:bg-background' => ! $isActive])>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted text-[12px] font-semibold text-ju-navy">
                            @if ($c->participants->count() > 2)
                                <i data-lucide="users" class="size-4"></i>
                            @else
                                {{ \Illuminate\Support\Str::of($c->titleFor(auth()->user()))->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-baseline justify-between gap-2">
                                <span @class(['truncate text-sm text-foreground', 'font-semibold' => $c->unread, 'font-medium' => ! $c->unread])>{{ $c->titleFor(auth()->user()) }}</span>
                                @if ($c->last_message_at)
                                    <span class="shrink-0 text-[11px] text-muted-foreground">{{ $c->last_message_at->shortRelativeDiffForHumans() }}</span>
                                @endif
                            </span>
                            <span class="flex items-center justify-between gap-2">
                                <span class="truncate text-[13px] text-muted-foreground">
                                    @if ($last)
                                        {{ (int) $last->user_id === (int) auth()->id() ? 'You: ' : '' }}{{ \Illuminate\Support\Str::limit($last->body, 60) }}
                                    @endif
                                </span>
                                @if ($c->unread)
                                    <span class="tabular flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-semibold text-white">{{ $c->unread }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
