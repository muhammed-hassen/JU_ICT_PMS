@extends('layouts.console')

@section('title', 'Projects')

@section('content_header')
    <x-ui.page-header title="Projects" description="Every ICT project, its template, teams and progress.">
        @canvisit(route('admin.projects.create'))
            <x-ui.button :href="route('admin.projects.create')">
                <i data-lucide="plus" class="size-4"></i>
                New project
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')
@php
    $activeFilters = collect([
        'search'      => request('search'),
        'status'      => request('status'),
        'template_id' => request('template_id'),
        'team_id'     => request('team_id'),
        'creator_id'  => request('creator_id'),
        'overdue'     => request('overdue'),
        'progress_min'=> request('progress_min'),
        'progress_max'=> request('progress_max'),
    ])->filter()->count();

    $currentSort = request('sort', 'created_at');
    $currentDir  = request('dir', 'desc');

    $statusOptions = [
        'active'    => ['label' => 'Active',    'variant' => 'primary'],
        'completed' => ['label' => 'Completed', 'variant' => 'success'],
        'draft'     => ['label' => 'Draft',     'variant' => 'neutral'],
        'on_hold'   => ['label' => 'On Hold',   'variant' => 'neutral'],
    ];
@endphp

{{-- ── Filter bar ─────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.projects.index') }}" id="filter-form">

    <div class="mb-4 rounded-xl border border-border bg-card shadow-sm">

        {{-- Top row: search + quick status tabs + sort --}}
        <div class="flex flex-wrap items-center gap-3 border-b border-border px-4 py-3">

            {{-- Search --}}
            <div class="relative flex-1 min-w-52" id="search-wrapper">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground z-10"></i>
                <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                       placeholder="Search projects…"
                       autocomplete="off"
                       class="h-9 w-full rounded-lg border border-border bg-background pl-9 pr-3 text-[13px] text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
                {{-- Live results dropdown --}}
                <ul id="search-dropdown"
                    class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-96 overflow-y-auto rounded-xl border border-border bg-popover shadow-lg">
                </ul>
            </div>

            {{-- Status quick-filter --}}
            <div class="flex items-center gap-1 rounded-lg border border-border bg-muted p-0.5">
                <a href="{{ route('admin.projects.index', array_merge(request()->except(['status','page']), [])) }}"
                   class="rounded-md px-3 py-1 text-[12px] font-semibold no-underline transition
                          @if(! request('status')) bg-card text-foreground shadow-sm @else text-muted-foreground hover:text-foreground @endif">
                    All
                </a>
                @foreach($statusOptions as $val => $opt)
                    <a href="{{ route('admin.projects.index', array_merge(request()->except(['status','page']), ['status' => $val])) }}"
                       class="rounded-md px-3 py-1 text-[12px] font-semibold no-underline transition
                              @if(request('status') === $val) bg-card text-foreground shadow-sm @else text-muted-foreground hover:text-foreground @endif">
                        {{ $opt['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Sort --}}
            <div class="flex items-center gap-1.5">
                <label class="text-[12px] text-muted-foreground">Sort:</label>
                <select name="sort" onchange="this.form.submit()"
                        class="h-9 rounded-lg border border-border bg-background px-2 text-[13px] text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="created_at" @selected($currentSort === 'created_at')>Newest</option>
                    <option value="name"       @selected($currentSort === 'name')>Name</option>
                    <option value="progress"   @selected($currentSort === 'progress')>Progress</option>
                    <option value="end_date"   @selected($currentSort === 'end_date')>End date</option>
                    <option value="start_date" @selected($currentSort === 'start_date')>Start date</option>
                </select>
                <a href="{{ route('admin.projects.index', array_merge(request()->all(), ['dir' => $currentDir === 'asc' ? 'desc' : 'asc'])) }}"
                   class="flex size-9 items-center justify-center rounded-lg border border-border bg-background text-muted-foreground hover:text-foreground no-underline"
                   title="Toggle direction">
                    <i data-lucide="{{ $currentDir === 'asc' ? 'arrow-up-narrow-wide' : 'arrow-down-wide-narrow' }}" class="size-4"></i>
                </a>
            </div>

            {{-- Toggle advanced --}}
            <button type="button" id="toggle-advanced"
                    class="flex items-center gap-1.5 rounded-lg border border-border bg-background px-3 py-1.5 text-[13px] font-medium text-foreground hover:bg-muted">
                <i data-lucide="sliders-horizontal" class="size-4"></i>
                Filters
                @if($activeFilters > 0)
                    <span class="flex size-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">{{ $activeFilters }}</span>
                @endif
            </button>
        </div>

        {{-- Advanced filters panel (hidden by default unless active) --}}
        <div id="advanced-filters" class="{{ $activeFilters > 0 ? '' : 'hidden' }} border-b border-border px-4 py-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Template --}}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-muted-foreground">Template</label>
                    <select name="template_id"
                            class="h-9 w-full rounded-lg border border-border bg-background px-2.5 text-[13px] text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">All templates</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ $tpl->id }}" @selected(request('template_id') == $tpl->id)>{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Team --}}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-muted-foreground">Team</label>
                    <select name="team_id"
                            class="h-9 w-full rounded-lg border border-border bg-background px-2.5 text-[13px] text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">All teams</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(request('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Creator --}}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-muted-foreground">Created by</label>
                    <select name="creator_id"
                            class="h-9 w-full rounded-lg border border-border bg-background px-2.5 text-[13px] text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Anyone</option>
                        @foreach ($creators as $creator)
                            <option value="{{ $creator->id }}" @selected(request('creator_id') == $creator->id)>{{ $creator->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Overdue --}}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-muted-foreground">Health</label>
                    <select name="overdue"
                            class="h-9 w-full rounded-lg border border-border bg-background px-2.5 text-[13px] text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">All</option>
                        <option value="1" @selected(request('overdue') === '1')>Overdue only</option>
                    </select>
                </div>

                {{-- Progress range --}}
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[12px] font-semibold text-muted-foreground">
                        Progress range
                        <span id="progress-label" class="ml-1 font-normal text-muted-foreground">
                            {{ request('progress_min', 0) }}% – {{ request('progress_max', 100) }}%
                        </span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="range" name="progress_min" min="0" max="100" step="5"
                               value="{{ request('progress_min', 0) }}"
                               class="flex-1 accent-[hsl(var(--primary))]"
                               oninput="updateProgressLabel()" id="progress-min" />
                        <span class="text-[12px] text-muted-foreground">to</span>
                        <input type="range" name="progress_max" min="0" max="100" step="5"
                               value="{{ request('progress_max', 100) }}"
                               class="flex-1 accent-[hsl(var(--primary))]"
                               oninput="updateProgressLabel()" id="progress-max" />
                    </div>
                </div>

                {{-- Apply + Clear --}}
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button type="submit"
                            class="flex h-9 items-center gap-1.5 rounded-lg bg-primary px-4 text-[13px] font-semibold text-primary-foreground hover:bg-primary/90">
                        <i data-lucide="filter" class="size-4"></i>
                        Apply filters
                    </button>
                    @if ($activeFilters > 0)
                        <a href="{{ route('admin.projects.index') }}"
                           class="flex h-9 items-center gap-1.5 rounded-lg border border-border px-4 text-[13px] font-medium text-muted-foreground no-underline hover:bg-muted hover:text-foreground">
                            <i data-lucide="x" class="size-4"></i>
                            Clear all
                        </a>
                    @endif
                </div>
            </div>

            {{-- Active filter chips --}}
            @if($activeFilters > 0)
                <div class="mt-3 flex flex-wrap gap-2">
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            <i data-lucide="search" class="size-3"></i>
                            "{{ request('search') }}"
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['search','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['status','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                    @if(request('template_id'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            Template: {{ $templates->firstWhere('id', request('template_id'))?->name ?? '—' }}
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['template_id','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                    @if(request('team_id'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            Team: {{ $teams->firstWhere('id', request('team_id'))?->name ?? '—' }}
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['team_id','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                    @if(request('creator_id'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            By: {{ $creators->firstWhere('id', request('creator_id'))?->name ?? '—' }}
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['creator_id','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                    @if(request('overdue') === '1')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-destructive/10 px-3 py-1 text-[12px] font-medium text-destructive">
                            <i data-lucide="triangle-alert" class="size-3"></i>
                            Overdue
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['overdue','page']))) }}" class="ml-0.5 text-destructive/60 no-underline hover:text-destructive">×</a>
                        </span>
                    @endif
                    @if(request('progress_min') || request('progress_max'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-[12px] font-medium text-primary">
                            Progress: {{ request('progress_min', 0) }}–{{ request('progress_max', 100) }}%
                            <a href="{{ route('admin.projects.index', array_merge(request()->except(['progress_min','progress_max','page']))) }}" class="ml-0.5 text-primary/60 no-underline hover:text-primary">×</a>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Results meta --}}
        <div class="flex items-center justify-between gap-3 px-5 py-3">
            <p class="m-0 text-[13px] text-muted-foreground">
                Showing <span class="font-semibold text-foreground">{{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }}</span>
                of <span class="font-semibold text-foreground">{{ $projects->total() }}</span> project{{ $projects->total() !== 1 ? 's' : '' }}
            </p>
            {{-- hidden status input if coming from tab --}}
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}" />
            @endif
        </div>
    </div>

</form>

{{-- ── Projects table ──────────────────────────────────────── --}}
<x-ui.card flush>
    @if ($projects->isEmpty())
        <x-ui.empty-state icon="folder-kanban" title="No projects found"
                          description="{{ $activeFilters > 0 ? 'No projects match the current filters.' : 'Projects you create will be listed here.' }}">
            @if ($activeFilters > 0)
                <x-ui.button variant="outline" :href="route('admin.projects.index')">
                    <i data-lucide="x" class="size-4"></i>
                    Clear filters
                </x-ui.button>
            @else
                @canvisit(route('admin.projects.create'))
                    <x-ui.button :href="route('admin.projects.create')">
                        <i data-lucide="plus" class="size-4"></i>
                        Create your first project
                    </x-ui.button>
                @endcanvisit
            @endif
        </x-ui.empty-state>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted">
                    <tr>
                        {{-- Sortable columns --}}
                        @php
                            $sortLink = fn($field, $label) =>
                                '<a href="'.route('admin.projects.index', array_merge(request()->except(['sort','dir','page']), [
                                    'sort' => $field,
                                    'dir'  => ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc',
                                ])).'" class="inline-flex items-center gap-1 no-underline text-muted-foreground hover:text-foreground font-semibold">'
                                . $label
                                . ($currentSort === $field ? '<i data-lucide="'.($currentDir === 'asc' ? 'chevron-up' : 'chevron-down').'" style="width:12px;height:12px;"></i>' : '')
                                . '</a>';
                        @endphp
                        <x-ui.th>{!! $sortLink('name', 'Project') !!}</x-ui.th>
                        <x-ui.th>Template</x-ui.th>
                        <x-ui.th>Status</x-ui.th>
                        <x-ui.th class="text-right">Phases</x-ui.th>
                        <x-ui.th class="text-right">Teams</x-ui.th>
                        <x-ui.th class="min-w-44">{!! $sortLink('progress', 'Progress') !!}</x-ui.th>
                        <x-ui.th>{!! $sortLink('end_date', 'Deadline') !!}</x-ui.th>
                        <x-ui.th>Created by</x-ui.th>
                        <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        @php
                            $statusVariant = ['active' => 'primary', 'completed' => 'success'][$project->status] ?? 'neutral';
                            $isOverdue = $project->end_date && $project->end_date->isPast() && $project->status !== 'completed';
                        @endphp
                        <tr class="cursor-pointer border-b border-border/70 last:border-0 hover:bg-muted/50 @if($isOverdue) bg-destructive/[0.02] @endif"
                            onclick="window.location='{{ route('admin.projects.show', $project) }}'">

                            {{-- Name --}}
                            <x-ui.td class="min-w-56">
                                <a href="{{ route('admin.projects.show', $project) }}"
                                   class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline"
                                   onclick="event.stopPropagation()">
                                    {{ $project->name }}
                                </a>
                                @if ($project->description)
                                    <p class="m-0 text-[12px] text-muted-foreground">{{ Str::limit($project->description, 55) }}</p>
                                @endif
                            </x-ui.td>

                            {{-- Template --}}
                            <x-ui.td class="text-[13px] text-muted-foreground">
                                {{ $project->template?->name ?? '—' }}
                            </x-ui.td>

                            {{-- Status --}}
                            <x-ui.td>
                                <x-ui.badge :variant="$statusVariant">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</x-ui.badge>
                                @if ($isOverdue)
                                    <span class="ml-1 inline-flex items-center gap-0.5 text-[11px] font-semibold text-destructive">
                                        <i data-lucide="triangle-alert" class="size-3"></i> Overdue
                                    </span>
                                @endif
                            </x-ui.td>

                            {{-- Phases --}}
                            <x-ui.td class="tabular text-right">{{ $project->phases_count }}</x-ui.td>

                            {{-- Teams --}}
                            <x-ui.td class="tabular text-right">{{ $project->teams->count() }}</x-ui.td>

                            {{-- Progress --}}
                            <x-ui.td class="min-w-44">
                                <div class="flex items-center gap-2">
                                    <x-ui.progress class="flex-1" :value="$project->progress_percentage ?? 0" />
                                    <span class="w-9 shrink-0 text-right text-[12px] tabular-nums text-muted-foreground">
                                        {{ round($project->progress_percentage ?? 0) }}%
                                    </span>
                                </div>
                            </x-ui.td>

                            {{-- Deadline --}}
                            <x-ui.td class="whitespace-nowrap text-[13px] @if($isOverdue) font-semibold text-destructive @else text-muted-foreground @endif">
                                {{ $project->end_date?->format('M d, Y') ?? '—' }}
                            </x-ui.td>

                            {{-- Creator --}}
                            <x-ui.td class="whitespace-nowrap text-[13px] text-muted-foreground">
                                {{ $project->creator?->name ?? 'N/A' }}
                            </x-ui.td>

                            {{-- Actions --}}
                            <x-ui.td>
                                <div class="flex items-center justify-end gap-0.5" onclick="event.stopPropagation()">
                                    <x-ui.icon-button icon="eye" label="View project" :href="route('admin.projects.show', $project)" />
                                    @canvisit(route('admin.projects.edit', $project))
                                        <x-ui.icon-button icon="pencil" label="Edit project" :href="route('admin.projects.edit', $project)" />
                                    @endcanvisit
                                    @canvisit(route('admin.projects.destroy', $project), 'DELETE')
                                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="m-0"
                                              onsubmit="return confirm('Delete this project? All phases and tasks will be deleted.')">
                                            @csrf @method('DELETE')
                                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete project" tone="destructive" />
                                        </form>
                                    @endcanvisit
                                </div>
                            </x-ui.td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $projects->links() }}</div>
            </x-slot:footer>
        @endif
    @endif
</x-ui.card>

@push('js')
<script>
(function () {
    var searchInput   = document.getElementById('search-input');
    var dropdown      = document.getElementById('search-dropdown');
    var form          = document.getElementById('filter-form');
    var searchUrl     = '{{ route('admin.projects.search') }}';
    var debounceTimer = null;
    var activeIndex   = -1;

    var statusColors = {
        active:    'bg-blue-100 text-blue-700',
        completed: 'bg-green-100 text-green-700',
        draft:     'bg-muted text-muted-foreground',
        on_hold:   'bg-yellow-100 text-yellow-700',
    };

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function highlight(text, q) {
        if (!q) return escHtml(text);
        var escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return escHtml(text).replace(new RegExp('(' + escaped + ')', 'gi'),
            '<mark class="bg-primary/20 text-primary rounded px-0.5">$1</mark>');
    }

    function renderResults(results, q) {
        dropdown.innerHTML = '';
        activeIndex = -1;

        if (results.length === 0) {
            dropdown.innerHTML =
                '<li class="flex items-center gap-2 px-4 py-3 text-[13px] text-muted-foreground">' +
                '<i data-lucide="search-x" style="width:16px;height:16px;"></i>' +
                'No projects found for <strong class="ml-1">"' + escHtml(q) + '"</strong></li>';
            dropdown.classList.remove('hidden');
            if (window.lucide) lucide.createIcons({ nodes: dropdown.querySelectorAll('[data-lucide]') });
            return;
        }

        results.forEach(function (p, i) {
            var statusClass = statusColors[p.status] || 'bg-muted text-muted-foreground';
            var li = document.createElement('li');
            li.setAttribute('data-url', p.url);
            li.setAttribute('data-index', i);
            li.className = 'group flex cursor-pointer items-start gap-3 border-b border-border/50 px-4 py-3 last:border-0 hover:bg-muted/60 transition-colors';
            li.innerHTML =
                // Icon column
                '<span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10">' +
                '<i data-lucide="folder" style="width:15px;height:15px;" class="text-primary"></i></span>' +
                // Content
                '<span class="min-w-0 flex-1">' +
                  '<span class="flex items-center gap-2">' +
                    '<span class="truncate text-[13px] font-semibold text-foreground">' + highlight(p.name, q) + '</span>' +
                    '<span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ' + statusClass + '">' + escHtml(p.status.replace('_',' ')) + '</span>' +
                  '</span>' +
                  (p.description ? '<span class="mt-0.5 block truncate text-[12px] text-muted-foreground">' + highlight(p.description, q) + '</span>' : '') +
                  '<span class="mt-1 flex items-center gap-3 text-[11px] text-muted-foreground">' +
                    (p.template ? '<span>' + escHtml(p.template) + '</span>' : '') +
                    '<span>' + p.phases + ' phase' + (p.phases !== 1 ? 's' : '') + '</span>' +
                    '<span>' + p.progress + '% done</span>' +
                  '</span>' +
                '</span>' +
                // Arrow
                '<i data-lucide="arrow-right" style="width:14px;height:14px;" class="mt-1 shrink-0 text-muted-foreground/40 group-hover:text-primary transition-colors"></i>';

            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                window.location.href = p.url;
            });
            dropdown.appendChild(li);
        });

        // "Search all results" footer
        var footer = document.createElement('li');
        footer.className = 'flex cursor-pointer items-center justify-between px-4 py-2.5 bg-muted/40 text-[12px] font-semibold text-primary hover:bg-muted transition-colors';
        footer.innerHTML = '<span>Search all results for <strong>"' + escHtml(q) + '"</strong></span><i data-lucide="corner-down-left" style="width:13px;height:13px;"></i>';
        footer.addEventListener('mousedown', function (e) {
            e.preventDefault();
            searchInput.value = q;
            form.submit();
        });
        dropdown.appendChild(footer);

        dropdown.classList.remove('hidden');
        if (window.lucide) lucide.createIcons({ nodes: dropdown.querySelectorAll('[data-lucide]') });
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        activeIndex = -1;
    }

    function fetchResults(q) {
        if (q.length < 1) { closeDropdown(); return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderResults(data, q); })
        .catch(function () { closeDropdown(); });
    }

    // Debounced input
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = searchInput.value.trim();
        if (q.length < 1) { closeDropdown(); return; }
        debounceTimer = setTimeout(function () { fetchResults(q); }, 220);
    });

    // Show existing results when re-focused
    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length >= 1 && dropdown.children.length > 0) {
            dropdown.classList.remove('hidden');
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function (e) {
        var items = dropdown.querySelectorAll('li[data-url]');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            items.forEach(function (li, i) { li.classList.toggle('bg-muted/60', i === activeIndex); });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, -1);
            items.forEach(function (li, i) { li.classList.toggle('bg-muted/60', i === activeIndex); });
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && items[activeIndex]) {
                e.preventDefault();
                window.location.href = items[activeIndex].getAttribute('data-url');
            } else {
                e.preventDefault();
                form.submit();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        if (! document.getElementById('search-wrapper').contains(e.target)) {
            closeDropdown();
        }
    });

    // Toggle advanced filters panel
    document.getElementById('toggle-advanced').addEventListener('click', function () {
        document.getElementById('advanced-filters').classList.toggle('hidden');
    });

    // Update progress range label live
    window.updateProgressLabel = function () {
        var min = document.getElementById('progress-min').value;
        var max = document.getElementById('progress-max').value;
        document.getElementById('progress-label').textContent = min + '% – ' + max + '%';
    };
})();
</script>
@endpush

@endsection
