@extends('layouts.console')
@section('title', 'Messages')

@section('content_header')
    <x-ui.page-header title="Messages" description="Talk with your team, team leader and the ICT Director." />
@endsection

@section('content')
    <x-ui.card flush class="overflow-hidden">
        <div class="grid min-h-[560px] lg:grid-cols-[320px_1fr]">
            @include('messages.partials.list')
            <div class="hidden lg:block">
                <x-ui.empty-state icon="messages-square" title="Pick a conversation" description="Choose one on the left, or start a new one.">
                    @can('create-conversation')
                        <x-ui.button :href="route('messages.create')">
                            <i data-lucide="square-pen" class="size-4"></i>
                            New message
                        </x-ui.button>
                    @endcan
                </x-ui.empty-state>
            </div>
        </div>
    </x-ui.card>
@endsection
