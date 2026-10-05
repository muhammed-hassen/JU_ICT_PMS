{{-- resources/views/admin/activity/task.blade.php --}}
@extends('layouts.console')

@section('title', "Activity - {$task->title}")

@section('content_header')
    <x-ui.page-header :title="'Activity: ' . $task->title" description="Every recorded change to this task.">
        @canvisit(route('admin.tasks.show', $task))
            <x-ui.button variant="outline" :href="route('admin.tasks.show', $task)">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to task
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.card title="Activity feed" flush>
        @if ($activities->isEmpty())
            <x-ui.empty-state icon="inbox" title="No activities found for this task." />
        @else
            @include('admin.activity._feed', ['activities' => $activities])
        @endif

        @if ($activities->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $activities->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
