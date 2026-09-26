{{--
    Shown instead of a create form when the level above does not exist yet
    (a phase needs a project, a task needs a phase). Tells the user what to
    create first and links straight to it.
--}}
@extends('layouts.console')

@section('title', $title)

@section('content_header')
    <x-ui.page-header :title="$title" />
@endsection

@section('content')
    <x-ui.card>
        <x-ui.empty-state :icon="$icon" :title="$heading" :description="$message">
            @if ($actionRoute)
                @canvisit($actionRoute)
                    <x-ui.button :href="$actionRoute">
                        <i data-lucide="plus" class="size-4"></i>
                        {{ $actionLabel }}
                    </x-ui.button>
                @else
                    <p class="m-0 text-sm text-muted-foreground">Ask your Team Leader or the ICT Director to set this up.</p>
                @endcanvisit
            @endif
        </x-ui.empty-state>
    </x-ui.card>
@endsection
