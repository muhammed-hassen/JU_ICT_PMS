{{-- resources/views/admin/activity/project.blade.php --}}
@extends('layouts.console')

@section('title', "Activity - {$project->name}")

@section('content_header')
    <x-ui.page-header :title="'Activity: ' . $project->name" description="Changes to this project, its phases and its tasks.">
        @canvisit(route('admin.projects.show', $project))
            <x-ui.button variant="outline" :href="route('admin.projects.show', $project)">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to project
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <x-ui.card title="Activity feed" flush>
        @if ($activities->isEmpty())
            <x-ui.empty-state icon="inbox" title="No activities found for this project." />
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
