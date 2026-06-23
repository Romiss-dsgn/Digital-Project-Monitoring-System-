<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProjectAccomplishmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creating_accomplishment_updates_project_progress(): void
    {
        $user = $this->authorizedUser(['view', 'create']);
        Passport::actingAs($user);
        $project = Project::create([
            'project_code' => 'ACC-PROJ-' . uniqid(),
            'project_name' => 'Accomplishment Test Project',
            'status' => 'Ongoing',
            'progress_percent' => 0,
            'is_archived' => false,
        ]);

        $response = $this->postJson('/api/v2/project-accomplishments', [
            'project_id' => $project->id,
            'milestone_title' => 'Foundation Works',
            'description' => 'Feature test milestone',
            'target_date' => '2026-12-01',
            'percent_complete' => 60,
            'status' => 'In Progress',
        ]);

        $response->assertCreated()->assertJsonPath('data.percent_complete', 60);
        $this->assertEquals(60.0, (float) $project->fresh()->progress_percent);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'project_accomplishments',
            'action' => 'created',
        ]);
    }

    public function test_summary_is_calculated_from_accomplishment_records(): void
    {
        $user = $this->authorizedUser(['view']);
        Passport::actingAs($user);

        $this->getJson('/api/v2/project-accomplishments/summary')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'overall_progress',
                    'milestones_completed',
                    'milestones_total',
                    'delayed_tasks',
                    'recent_activity',
                ],
            ]);
    }

    public function test_accomplishment_can_be_validated_and_archived(): void
    {
        $user = $this->authorizedUser(['approve', 'delete']);
        Passport::actingAs($user);
        $project = Project::create([
            'project_code' => 'VALIDATE-PROJ-' . uniqid(),
            'project_name' => 'Validation Test Project',
            'status' => 'Ongoing',
            'is_archived' => false,
        ]);
        $accomplishment = ProjectAccomplishment::create([
            'project_id' => $project->id,
            'milestone_title' => 'Validation Milestone',
            'target_date' => '2026-12-01',
            'percent_complete' => 80,
            'status' => 'In Progress',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/project-accomplishments/' . $accomplishment->id . '/validate')
            ->assertOk();
        $this->assertEquals($user->id, $accomplishment->fresh()->validated_by);

        $this->deleteJson('/api/v2/project-accomplishments/' . $accomplishment->id)->assertOk();
        $this->assertTrue($accomplishment->fresh()->is_archived);
    }

    private function authorizedUser(array $actions): User
    {
        $role = Role::create(['name' => 'Test Accomplishment ' . uniqid(), 'description' => 'Feature test role']);
        $permission = ['role_id' => $role->id, 'module' => 'project_accomplishments'];

        foreach (['view', 'create', 'edit', 'delete', 'approve', 'export'] as $action) {
            $permission['can_' . $action] = in_array($action, $actions, true);
        }

        RolePermission::create($permission);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
