@extends('layouts.console')
@section('title', 'Notifications')

@section('content_header')
    <x-ui.page-header title="Notifications" description="Tasks assigned to you and status changes on your work.">
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                @csrf
                <x-ui.button type="submit" variant="outline">
                    <i data-lucide="check-check" class="size-4"></i>
                    Mark all read
                </x-ui.button>
            </form>
        @endif
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.card flush>
        @if ($notifications->isEmpty())
            <x-ui.empty-state icon="bell" title="No notifications yet" description="When someone assigns you a task or moves one of yours, it shows up here." />
        @else
            <ul class="m-0 list-none divide-y divide-border p-0">
                @foreach ($notifications as $note)
                    <li>
                        <a href="{{ route('notifications.open', $note->id) }}"
                           @class(['flex items-start gap-3 px-5 py-3.5 no-underline hover:bg-background hover:no-underline', 'bg-primary/5' => ! $note->read_at])>
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                <i data-lucide="{{ $note->data['icon'] ?? 'bell' }}" class="size-4"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-foreground">{{ $note->data['title'] ?? 'Notification' }}</span>
                                <span class="block text-[13px] text-muted-foreground">{{ $note->data['body'] ?? '' }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-muted-foreground" title="{{ $note->created_at->format('M j, Y g:i A') }}">{{ $note->created_at->diffForHumans() }}</span>
                            @unless ($note->read_at)
                                <span class="mt-1.5 size-2 shrink-0 rounded-full bg-primary" aria-label="Unread"></span>
                            @endunless
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($notifications->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $notifications->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
