@extends('layouts.console')

@section('title', 'Project Templates')

@section('content_header')
    <x-ui.page-header title="Project Templates" description="Manage the default phases and tasks new projects start from.">
        @canvisit(route('admin.templates.create'))
            <x-ui.button :href="route('admin.templates.create')">
                <i data-lucide="plus" class="size-4"></i>
                Create Template
            </x-ui.button>
        @endcanvisit
    </x-ui.page-header>
@endsection

@section('content')

    {{-- template-count-label and template-index-table are hooks the feature tests look for. --}}
    <x-ui.card flush>
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h3 class="m-0 font-sans text-[15px] font-semibold">Template Catalog</h3>
            <x-ui.badge class="template-count-label">
                {{ $templates->total() }} {{ \Illuminate\Support\Str::plural('template', $templates->total()) }} available
            </x-ui.badge>
        </div>

        @if ($templates->count())
            <div class="overflow-x-auto">
                <table class="template-index-table w-full text-sm">
                    <thead class="border-b border-border bg-muted">
                        <tr>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Description</x-ui.th>
                            <x-ui.th class="text-right">Phases</x-ui.th>
                            <x-ui.th class="text-right">Tasks</x-ui.th>
                            <x-ui.th>Status</x-ui.th>
                            <x-ui.th class="text-right"><span class="sr-only">Actions</span></x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($templates as $template)
                            <tr class="border-b border-border/70 last:border-0 hover:bg-background">
                                <x-ui.td class="min-w-48 font-semibold text-foreground">{{ $template->name }}</x-ui.td>
                                <x-ui.td class="text-[13px] text-muted-foreground">{{ $template->description ?: 'N/A' }}</x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $template->phases_count }}</x-ui.td>
                                <x-ui.td class="tabular text-right">{{ $template->tasks_count }}</x-ui.td>
                                <x-ui.td>
                                    @if ($template->is_active)
                                        <x-ui.badge variant="success">Active</x-ui.badge>
                                    @else
                                        <x-ui.badge>Inactive</x-ui.badge>
                                    @endif
                                </x-ui.td>
                                <x-ui.td>
                                    <div class="flex items-center justify-end gap-0.5">
                                        @canvisit(route('admin.templates.edit', $template))
                                            <x-ui.icon-button icon="pencil" label="Edit template" :href="route('admin.templates.edit', $template)" />
                                        @endcanvisit
                                        @canvisit(route('admin.templates.destroy', $template), 'DELETE')
                                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="m-0"
                                                  onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.icon-button type="submit" icon="trash-2" label="Delete template" tone="destructive" />
                                            </form>
                                        @endcanvisit
                                    </div>
                                </x-ui.td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state icon="folder-open" title="No templates yet" description="Create your first template to get started.">
                @canvisit(route('admin.templates.create'))
                    <x-ui.button :href="route('admin.templates.create')">
                        <i data-lucide="plus" class="size-4"></i>
                        Create First Template
                    </x-ui.button>
                @endcanvisit
            </x-ui.empty-state>
        @endif

        @if ($templates->hasPages())
            <x-slot:footer>
                <div class="flex w-full flex-wrap items-center justify-between gap-3">
                    <span class="tabular text-muted-foreground">
                        Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} templates
                    </span>
                    <div>{{ $templates->links() }}</div>
                </div>
            </x-slot:footer>
        @endif
    </x-ui.card>
@endsection
