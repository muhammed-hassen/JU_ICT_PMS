@extends('layouts.console')
@section('title', $conversation->titleFor(auth()->user()) . ' · Messages')

@section('content_header')
    <x-ui.page-header title="Messages" description="Talk with your team, team leader and the ICT Director." />
@endsection

@section('content')
    <x-ui.card flush class="overflow-hidden">
        <div class="grid h-[calc(100vh-15rem)] min-h-[520px] lg:grid-cols-[320px_1fr]">
            <div class="hidden min-h-0 lg:flex lg:flex-col">
                @include('messages.partials.list')
            </div>

            <section class="flex min-h-0 flex-col" aria-label="Conversation">
                <header class="flex items-center gap-3 border-b border-border px-5 py-3">
                    <a href="{{ route('messages.index') }}" class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted lg:hidden" aria-label="Back to conversations">
                        <i data-lucide="arrow-left" class="size-4"></i>
                    </a>
                    <div class="min-w-0">
                        <h2 class="m-0 truncate font-sans text-[15px] font-semibold">{{ $conversation->titleFor(auth()->user()) }}</h2>
                        <p class="m-0 truncate text-[13px] text-muted-foreground">
                            {{ $conversation->participants->pluck('name')->join(', ') }}
                        </p>
                    </div>
                </header>

                <div id="message-list" class="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-5 py-5">
                    @forelse ($conversation->messages as $message)
                        @include('messages.partials.message')
                    @empty
                        <p id="no-messages" class="m-auto text-sm text-muted-foreground">No messages yet. Say hello.</p>
                    @endforelse
                </div>

                @can('send-message')
                    <form id="reply-form" method="POST" action="{{ route('messages.reply', $conversation) }}" class="flex items-end gap-2 border-t border-border px-5 py-3">
                        @csrf
                        <label for="reply-body" class="sr-only">Message</label>
                        <textarea id="reply-body" name="body" rows="1" required maxlength="5000" placeholder="Write a message. Enter sends, Shift+Enter adds a line."
                                  class="block max-h-40 min-h-10 w-full resize-none rounded-lg border border-input bg-card px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/15"></textarea>
                        <x-ui.button type="submit" aria-label="Send">
                            <i data-lucide="send" class="size-4"></i>
                            <span class="hidden sm:inline">Send</span>
                        </x-ui.button>
                    </form>
                    @error('body')
                        <p class="m-0 px-5 pb-3 text-[13px] text-destructive">{{ $message }}</p>
                    @enderror
                @endcan
            </section>
        </div>
    </x-ui.card>
@endsection

@push('js')
<script>
    (function () {
        var list = document.getElementById('message-list');
        var form = document.getElementById('reply-form');
        var box = document.getElementById('reply-body');
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var pollUrl = @json(route('messages.poll', $conversation));

        function lastId() {
            var items = list.querySelectorAll('[data-message-id]');
            return items.length ? items[items.length - 1].dataset.messageId : 0;
        }
        function append(html) {
            if (!html) return;
            var empty = document.getElementById('no-messages');
            if (empty) empty.remove();
            list.insertAdjacentHTML('beforeend', html);
            if (window.lucide) lucide.createIcons();
            list.scrollTop = list.scrollHeight;
        }

        list.scrollTop = list.scrollHeight;

        if (form) {
            // Enter sends, Shift+Enter makes a new line.
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
            });
            box.addEventListener('input', function () { box.style.height = 'auto'; box.style.height = box.scrollHeight + 'px'; });

            // Send without reloading the page; falls back to a normal post if fetch fails.
            form.addEventListener('submit', function (e) {
                if (!box.value.trim()) { e.preventDefault(); return; }
                e.preventDefault();
                var body = box.value;
                box.value = ''; box.style.height = 'auto';
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ body: body })
                }).then(function (r) { if (!r.ok) throw r; return r.json(); })
                  .then(function (data) { append(data.html); })
                  .catch(function () { box.value = body; form.submit(); });
            });
        }

        // Check for new messages from other people every 8 seconds while the page is open.
        setInterval(function () {
            if (document.hidden) return;
            fetch(pollUrl + '?after=' + lastId(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) { if (data) append(data.html); });
        }, 8000);
    })();
</script>
@endpush
