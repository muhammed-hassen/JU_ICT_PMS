{{-- Project files. Upload, download and delete follow the file permissions; see ProjectFileController. --}}
@php
    $files = $project->files()->with('uploader')->get();
    $canDeleteAny = auth()->user()->can('delete-any-files');
    $canDeleteOwn = auth()->user()->can('delete-own-files');
    $canDownload = auth()->user()->can('download-files');
@endphp

<x-ui.card class="mt-6" :title="'Files (' . $files->count() . ')'" description="Documents shared on this project." flush>
    @if ($files->isEmpty())
        <x-ui.empty-state icon="folder-open" title="No files yet" description="Specs, reports and designs uploaded here are visible to everyone on the project." />
    @else
        <ul class="m-0 list-none divide-y divide-border p-0">
            @foreach ($files as $file)
                <li class="flex items-center gap-3 px-5 py-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                        <i data-lucide="{{ $file->icon }}" class="size-4"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="m-0 truncate text-sm font-medium text-foreground">{{ $file->original_name }}</p>
                        <p class="m-0 text-[13px] text-muted-foreground">
                            {{ $file->readable_size }} · {{ $file->uploader->name ?? 'Unknown' }} · {{ $file->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @if ($canDownload)
                        <x-ui.icon-button icon="download" label="Download {{ $file->original_name }}" :href="route('admin.files.download', $file)" />
                    @endif
                    @if ($canDeleteAny || ($canDeleteOwn && (int) $file->uploaded_by === (int) auth()->id()))
                        <form action="{{ route('admin.files.destroy', $file) }}" method="POST" class="m-0"
                              onsubmit="return confirm('Delete this file? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <x-ui.icon-button type="submit" icon="trash-2" label="Delete {{ $file->original_name }}" tone="destructive" />
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @can('upload-project-files')
        <form action="{{ route('admin.projects.files.store', $project) }}" method="POST" enctype="multipart/form-data"
              class="flex flex-wrap items-center gap-3 border-t border-border px-5 py-4">
            @csrf
            <label for="project-file" class="sr-only">Choose a file</label>
            <input id="project-file" type="file" name="file" required
                   class="min-w-0 flex-1 text-sm text-muted-foreground file:mr-3 file:cursor-pointer file:rounded-lg file:border file:border-input file:bg-card file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted">
            <x-ui.button type="submit" variant="outline" size="sm">
                <i data-lucide="upload" class="size-4"></i>
                Upload
            </x-ui.button>
            @error('file')
                <p class="m-0 w-full text-[13px] text-destructive">{{ $message }}</p>
            @enderror
            <p class="m-0 w-full text-[13px] text-muted-foreground">PDF, Office documents, images or ZIP, up to 20 MB.</p>
        </form>
    @endcan
</x-ui.card>
