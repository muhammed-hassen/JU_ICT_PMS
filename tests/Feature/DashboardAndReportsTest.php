<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_each_role_lands_on_its_own_dashboard(): void
    {
        $this->actingAs($this->userWithRole('ICT Director'))->get(route('home'))->assertOk()->assertSee('Director dashboard');
        $this->actingAs($this->userWithRole('System Administrator'))->get(route('home'))->assertOk()->assertSee('System dashboard');
        $this->actingAs($this->userWithRole('Team Leader'))->get(route('home'))->assertOk()->assertSee('Team dashboard');
        $this->actingAs($this->userWithRole('Team Member'))->get(route('home'))->assertOk()->assertSee('My dashboard')->assertDontSee('Teams at a glance');
    }

    public function test_director_can_export_the_report_as_pdf_and_excel(): void
    {
        $director = $this->userWithRole('ICT Director');

        $pdf = $this->actingAs($director)->get(route('admin.analytics.export.pdf'));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $csv = $this->actingAs($director)->get(route('admin.analytics.export.excel'));
        $csv->assertOk()->assertDownload();
        $this->assertStringContainsString('Summary', $csv->streamedContent());
    }

    public function test_team_leader_can_export_but_a_member_cannot_open_reports(): void
    {
        $this->actingAs($this->userWithRole('Team Leader'))->get(route('admin.analytics.export.excel'))->assertOk();

        $member = $this->userWithRole('Team Member');
        $this->actingAs($member)->get(route('admin.analytics.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.analytics.export.pdf'))->assertForbidden();
    }
}
