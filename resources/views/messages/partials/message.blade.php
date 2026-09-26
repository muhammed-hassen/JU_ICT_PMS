{{-- One message bubble. Own messages sit on the right. --}}
@php
    $mine = (int) $message->user_id === (int) auth()->id();
    $canEdit = $mine && auth()->user()->can('edit-own-message');
    $canDelete = ($mine && auth()->user()->can('delete-own-message')) || auth()->user()->can('delete-any-message');
@endphp
<div id="message-{{ $message->id }}" data-message-id="{{ $message->id }}" @class(['group flex gap-2.5', 'flex-row-reverse' => $mine])>
    @unless ($mine)
        <span class="mt-5 flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-[11px] font-semibold text-ju-navy">
            {{ \Illuminate\Support\Str::of($message->sender->name ?? '?')->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}
        </span>
    @endunless
    <div @class(['flex max-w-[75%] flex-col', 'items-end' => $mine, 'items-start' => ! $mine])>
        <span class="mb-1 text-[11px] text-muted-foreground">
            {{ $mine ? 'You' : ($message->sender->name ?? 'Former user') }} · <span title="{{ $message->created_at->format('M j, Y g:i A') }}">{{ $message->created_at->format('M j, g:i A') }}</span>@if ($message->edited_at) · edited @endif
        </span>
        <div @class(['whitespace-pre-line break-words rounded-2xl px-3.5 py-2 text-sm leading-relaxed',
                     'rounded-tr-md bg-primary text-white' => $mine,
                     'rounded-tl-md bg-muted text-foreground' => ! $mine])>{{ $message->body }}</div>

        @if ($canEdit || $canDelete)
            <div class="mt-1 flex items-center gap-3 text-[12px] opacity-0 transition-opacity duration-200 focus-within:opacity-100 group-hover:opacity-100">
                @if ($canEdit)
                    <details class="relative">
                        <summary class="cursor-pointer list-none text-muted-foreground hover:text-foreground [&::-webkit-details-marker]:hidden">Edit</summary>
                        <form method="POST" action="{{ route('messages.update', $message) }}" class="absolute right-0 z-10 mt-1 w-72 rounded-lg border border-border bg-popover p-2 shadow-raised">
                            @csrf
                            @method('PATCH')
                            <label for="edit-{{ $message->id }}" class="sr-only">Edit message</label>
                            <textarea id="edit-{{ $message->id }}" name="body" rows="3" required maxlength="5000"
                                      class="block w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/15">{{ $message->body }}</textarea>
                            <div class="mt-2 flex justify-end"><x-ui.button type="submit" size="sm">Save</x-ui.button></div>
                        </form>
                    </details>
                @endif
                @if ($canDelete)
                    <form method="POST" action="{{ route('messages.destroy', $message) }}" class="m-0" onsubmit="return confirm('Delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="border-0 bg-transparent p-0 text-muted-foreground hover:text-destructive">Delete</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
