<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Files attached to a project. Anyone who can open the project and holds the
 * matching file permission can upload and download. People delete their own
 * uploads; delete-any-files covers the rest.
 */
class ProjectFileController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,png,jpg,jpeg,gif,webp,zip',
        ], [
            'file.max' => 'Files can be at most 20 MB.',
            'file.mimes' => 'That file type is not allowed.',
        ]);

        $upload = $request->file('file');

        $file = $project->files()->create([
            'uploaded_by' => $request->user()->id,
            'original_name' => $upload->getClientOriginalName(),
            'path' => $upload->store("project-files/{$project->id}", 'local'),
            'mime_type' => $upload->getClientMimeType(),
            'size' => $upload->getSize(),
        ]);

        $this->activityLog->log($project, 'updated', "File uploaded: {$file->original_name}", ['project_file_id' => $file->id]);

        return back()->with('success', 'File uploaded.');
    }

    public function download(ProjectFile $file): StreamedResponse
    {
        $this->authorizeProject($file->project);

        abort_unless(Storage::disk('local')->exists($file->path), 404, 'This file is missing from storage.');

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function destroy(ProjectFile $file): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeProject($file->project);

        $ownsFile = (int) $file->uploaded_by === (int) $user->id;
        if (! $user->can('delete-any-files') && ! ($ownsFile && $user->can('delete-own-files'))) {
            abort(403, 'You can only delete files you uploaded.');
        }

        $project = $file->project;
        $name = $file->original_name;
        $file->delete();

        $this->activityLog->log($project, 'updated', "File removed: {$name}");

        return back()->with('success', 'File removed.');
    }

    /** Same visibility rule as the project page. */
    private function authorizeProject(Project $project): void
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! in_array($project->id, $user->getVisibleProjectIds())) {
            abort(403, 'You do not have permission to view this project.');
        }
    }
}
