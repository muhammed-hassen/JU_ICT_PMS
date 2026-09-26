{{-- resources/views/admin/activity/user.blade.php --}}
@extends('layouts.console')

@section('title', "Activity - {$user->name}")

@section('content_header')
    <x-ui.page-header :title="'Activity: ' . $user->name" :description="$user->email">
        @canvisit(route('admin.activity.index'))
            <x-ui.button variant="outline" :href="route('admin.activity.index')">
                <i data-lucide="arrow-left" class="size-4"></i>
                Back to all activities
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total activities" :value="$stats['total']" icon="history" />
        <x-ui.stat-card label="Today" :value="$stats['today']" icon="calendar" />
        <x-ui.stat-card label="Project activities" :value="$stats['projects']" icon="folder-kanban" />
        <x-ui.stat-card label="Task activities" :value="$stats['tasks']" icon="list-checks" />
    </div>

    <x-ui.card class="mt-6" title="Recent activities" flush>
        @if ($activities->isEmpty())
            <x-ui.empty-state icon="inbox" title="No activities found for this user." />
        @else
            @include('admin.activity._feed', [
                'activities' => $activities,
                'showUser' => false,
                'showDetails' => false,
                'groupByDate' => false,
                'relativeTime' => true,
            ])
        @endif

        @if ($activities->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $activities->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
