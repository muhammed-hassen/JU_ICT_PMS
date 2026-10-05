@extends('layouts.console')
@section('title', 'New message')

@section('content_header')
    <x-ui.page-header title="Messages" description="Talk with your team, team leader and the ICT Director." />
@endsection

@section('content')
    <x-ui.card flush class="overflow-hidden">
        <div class="grid min-h-[560px] lg:grid-cols-[320px_1fr]">
            <div class="hidden min-h-0 lg:flex lg:flex-col">
                @include('messages.partials.list')
            </div>

            <form method="POST" action="{{ route('messages.store') }}" class="flex flex-col gap-5 p-5">
                @csrf
                <h2 class="m-0 font-sans text-[15px] font-semibold">New message</h2>

                @php $chosen = old('participants', $preselected); @endphp
                <fieldset class="m-0 border-0 p-0">
                    <legend class="mb-1.5 text-[13px] font-semibold text-foreground">To</legend>
                    <label for="people-filter" class="sr-only">Filter people</label>
                    <div class="relative mb-2">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"></i>
                        <x-ui.input id="people-filter" type="search" class="pl-9" placeholder="Filter people" autocomplete="off" />
                    </div>
                    @if ($people->isEmpty())
                        <p class="m-0 text-sm text-muted-foreground">There is nobody you can message yet. You need to be in a team first.</p>
                    @else
                        <ul id="people-list" class="m-0 grid max-h-64 list-none gap-1 overflow-y-auto rounded-lg border border-border p-2 sm:grid-cols-2">
                            @foreach ($people as $person)
                                <li data-name="{{ strtolower($person->name . ' ' . $person->email) }}">
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 hover:bg-muted">
                                        <input type="checkbox" name="participants[]" value="{{ $person->id }}" class="size-4 accent-[var(--primary)]"
                                               @checked(in_array($person->id, array_map('intval', (array) $chosen)))>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-foreground">{{ $person->name }}</span>
                                            <span class="block truncate text-xs text-muted-foreground">{{ $person->getRoleNames()->first() }}</span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @error('participants')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                    @error('participants.*')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </fieldset>

                <div>
                    <x-ui.label for="subject">Subject <span class="font-normal text-muted-foreground">(optional, for group topics)</span></x-ui.label>
                    <x-ui.input id="subject" name="subject" maxlength="150" :value="old('subject')" placeholder="e.g. Student portal launch" />
                </div>

                <div>
                    <x-ui.label for="body">Message</x-ui.label>
                    <textarea id="body" name="body" rows="5" required maxlength="5000"
                              class="block w-full rounded-lg border border-input bg-card px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/15">{{ old('body') }}</textarea>
                    @error('body')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="outline" :href="route('messages.index')">Cancel</x-ui.button>
                    <x-ui.button type="submit">
                        <i data-lucide="send" class="size-4"></i>
                        Send
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>
@endsection

@push('js')
<script>
    (function () {
        var filter = document.getElementById('people-filter');
        var items = document.querySelectorAll('#people-list li');
        if (!filter) return;
        filter.addEventListener('input', function () {
            var q = filter.value.trim().toLowerCase();
            items.forEach(function (li) { li.hidden = q && li.dataset.name.indexOf(q) === -1; });
        });
    })();
</script>
@endpush
