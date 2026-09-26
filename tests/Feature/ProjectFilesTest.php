<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectFilesTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');

        $this->member = User::factory()->create();
        $this->member->assignRole('Team Member');

        $team = Team::create(['name' => 'Files Team']);
        $this->member->teams()->attach($team);

        $this->project = Project::create(['name' => 'Portal', 'status' => 'active', 'created_by' => User::first()->id]);
        $this->project->teams()->attach($team);
    }

    public function test_member_can_upload_download_and_delete_own_file(): void
    {
        $this->actingAs($this->member)
            ->post(route('admin.projects.files.store', $this->project), ['file' => UploadedFile::fake()->create('spec.pdf', 120, 'application/pdf')])
            ->assertRedirect();

        $file = ProjectFile::firstOrFail();
        Storage::disk('local')->assertExists($file->path);

        $this->actingAs($this->member)->get(route('admin.projects.show', $this->project))->assertOk()->assertSee('spec.pdf');
        $this->actingAs($this->member)->get(route('admin.files.download', $file))->assertOk()->assertDownload('spec.pdf');

        $this->actingAs($this->member)->delete(route('admin.files.destroy', $file))->assertRedirect();
        $this->assertDatabaseMissing('project_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_member_cannot_delete_someone_elses_file(): void
    {
        $colleague = User::factory()->create();
        $colleague->assignRole('Team Member');
        $file = $this->project->files()->create(['uploaded_by' => $colleague->id, 'original_name' => 'a.txt', 'path' => 'x/a.txt', 'size' => 1]);

        $this->actingAs($this->member)->delete(route('admin.files.destroy', $file))->assertForbidden();
    }

    public function test_outsider_cannot_upload_or_download(): void
    {
        $outsider = User::factory()->create();
        $outsider->assignRole('Team Member');
        $file = $this->project->files()->create(['uploaded_by' => $this->member->id, 'original_name' => 'a.txt', 'path' => 'x/a.txt', 'size' => 1]);

        $this->actingAs($outsider)
            ->post(route('admin.projects.files.store', $this->project), ['file' => UploadedFile::fake()->create('x.pdf', 10)])
            ->assertForbidden();
        $this->actingAs($outsider)->get(route('admin.files.download', $file))->assertForbidden();
    }

    public function test_disallowed_file_type_is_rejected(): void
    {
        $this->actingAs($this->member)
            ->post(route('admin.projects.files.store', $this->project), ['file' => UploadedFile::fake()->create('run.exe', 10)])
            ->assertSessionHasErrors('file');
    }
}
