{{-- resources/views/admin/activity/index.blade.php --}}
@extends('layouts.console')

@section('title', 'Activity Log')

@section('content_header')
    <x-ui.page-header title="Activity Log" description="Track all system activities.">
        <x-ui.button variant="outline" onclick="window.location.reload()">
            <i data-lucide="refresh-cw" class="size-4"></i>
            Refresh
        </x-ui.button>
    </x-ui.page-header>
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total activities" :value="$stats['total']" icon="history" />
        <x-ui.stat-card label="Today" :value="$stats['today']" icon="calendar" />
        <x-ui.stat-card label="This week" :value="$stats['this_week']" icon="calendar-range" />
        <x-ui.stat-card label="This month" :value="$stats['this_month']" icon="calendar-days" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.activity.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-48">
            <x-ui.label for="filter-user">User</x-ui.label>
            <x-ui.select id="filter-user" name="user">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-44">
            <x-ui.label for="filter-action">Action</x-ui.label>
            <x-ui.select id="filter-action" name="action">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($action) }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-40">
            <x-ui.label for="filter-type">Type</x-ui.label>
            <x-ui.select id="filter-type" name="type">
                <option value="">All types</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full sm:w-44">
            <x-ui.label for="filter-from">From date</x-ui.label>
            <x-ui.input type="date" id="filter-from" name="from" :value="request('from')" />
        </div>
        <div class="w-full sm:w-44">
            <x-ui.label for="filter-to">To date</x-ui.label>
            <x-ui.input type="date" id="filter-to" name="to" :value="request('to')" />
        </div>
        <div class="flex gap-2">
            <x-ui.button type="submit">
                <i data-lucide="filter" class="size-4"></i>
                Filter
            </x-ui.button>
            @canvisit(route('admin.activity.index'))
                <x-ui.button variant="outline" :href="route('admin.activity.index')">Clear</x-ui.button>
            @endcanvisit
        </div>
    </form>

    <x-ui.card class="mt-5" flush>
        <div class="flex items-center gap-2 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Activity feed</h3>
            <x-ui.badge variant="primary">{{ $activities->total() }}</x-ui.badge>
        </div>

        @if ($activities->isEmpty())
            <x-ui.empty-state icon="inbox" title="No activities found." description="Try different filters or a wider date range." />
        @else
            @include('admin.activity._feed', ['activities' => $activities])
        @endif

        @if ($activities->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $activities->appends(request()->query())->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
