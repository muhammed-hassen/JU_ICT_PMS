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

    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">All projects</h3>
            <x-ui.badge>{{ $projects->total() }} total</x-ui.badge>
        </div>

        @if ($projects->isEmpty())
            <x-ui.empty-state icon="folder-kanban" title="No projects yet" description="Projects you create will be listed here.">
                @canvisit(route('admin.projects.create'))
                    <x-ui.button :href="route('admin.projects.create')">
                        <i data-lucide="plus" class="size-4"></i>
                        Create your first project
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Project</x-ui.th>
                            <x-ui.th>Template</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th class="text-right">Phases</x-ui.th>
                            <x-ui.th class="text-right">Teams</x-ui.th>
                            <x-ui.th class="min-w-44">Progress</x-ui.th>
                            <x-ui.th>Created by</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            @php
                                $statusVariant = [
                                    'active' => 'primary',
                                    'completed' => 'success',
                                ][$project->status] ?? 'neutral';
                            @endphp
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="min-w-56">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="font-semibold text-foreground no-underline hover:text-primary hover:no-underline">
                                        {{ $project->name }}
                                    </a>
                                    @if ($project->description)
                                        <p class="m-0 text-[13px] text-muted-foreground">{{ Str::limit($project->description, 50) }}</p>
                                    @endif
                                </x-ui.td>
                                <x-ui.td class="text-[13px] text-muted-foreground">
                                    {{ $project->template?->name ?? 'No template' }}
                                </x-ui.td>
                                <x-ui.td>
                                    <x-ui.badge :variant="$statusVariant">{{ ucfirst($project->status) }}</x-ui.badge>
                                </x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $project->phases_count }}</x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $project->teams->count() }}</x-ui.td>
                                <x-ui.td>
                                    <x-ui.progress :value="$project->progress_percentage ?? 0" />
                                </x-ui.td>
                                <x-ui.td class="whitespace-nowrap text-[13px] text-muted-foreground">
                                    {{ $project->creator?->name ?? 'N/A' }}
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        <x-ui.icon-button icon="eye" label="View project" :href="route('admin.projects.show', $project)" />
                                        @canvisit(route('admin.projects.edit', $project))
                                            <x-ui.icon-button icon="pencil" label="Edit project" :href="route('admin.projects.edit', $project)" />
                                        @endcanvisit
                                        @canvisit(route('admin.projects.destroy', $project), 'DELETE')
                                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="m-0"
                                                  onsubmit="return confirm('Delete this project? All phases and tasks will be deleted.')">
                                                @csrf
                                                @method('DELETE')
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
        @endif

        @if (isset($projects) && method_exists($projects, 'hasPages') && $projects->hasPages())
            <x-slot:footer>
                <div class="w-full">{{ $projects->links() }}</div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
