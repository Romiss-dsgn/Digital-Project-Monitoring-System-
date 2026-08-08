<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $engineerRole;
    protected User $admin;
    protected User $engineer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'System Administrator', 'description' => 'Admin']);
        $this->engineerRole = Role::create(['name' => 'Engineer - Planning', 'description' => 'Engineer']);

        RolePermission::create([
            'role_id' => $this->adminRole->id,
            'module' => 'projects',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);

        RolePermission::create([
            'role_id' => $this->engineerRole->id,
            'module' => 'projects',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => false,
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin@test.com',
            'password' => 'password',
            'role_id' => $this->adminRole->id,
            'is_active' => true,
        ]);

        $this->engineer = User::create([
            'name' => 'Engineer',
            'email' => 'engineer@test.com',
            'username' => 'engineer@test.com',
            'password' => 'password',
            'role_id' => $this->engineerRole->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_list_projects()
    {
        Project::create([
            'project_code' => 'TEST-001',
            'project_name' => 'Test Project',
            'location' => 'Municipal Hall Compound',
            'phase' => 'Planning',
            'status' => 'planning',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/v2/admin/projects');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'stats'])
            ->assertJsonPath('meta.permissions.can_create', true);
    }

    public function test_engineer_can_list_and_create_projects()
    {
        $response = $this->actingAs($this->engineer, 'api')
            ->postJson('/api/v2/admin/projects', [
                'code' => 'ENG-001',
                'name' => 'Engineer Project',
                'location' => 'Public Market Area',
                'new_contractor_name' => 'Engineer QA Contractor',
                'phase' => 'Planning',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'ENG-001');
    }

    public function test_engineer_cannot_delete_projects()
    {
        $project = Project::create([
            'project_code' => 'ENG-DEL-001',
            'project_name' => 'Delete Test',
            'location' => 'Municipal Hall Compound',
            'phase' => 'Planning',
            'status' => 'planning',
            'created_by' => $this->engineer->id,
        ]);

        $response = $this->actingAs($this->engineer, 'api')
            ->deleteJson("/api/v2/admin/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_create_project()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v2/admin/projects', [
                'code' => 'NEW-001',
                'name' => 'New Project',
                'location' => 'Rural Health Unit Compound',
                'new_contractor_name' => 'Admin QA Contractor',
                'phase' => 'Planning',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'NEW-001');
    }

    public function test_project_status_progress_and_duration_are_system_derived()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v2/admin/projects', [
                'code' => 'SYSTEM-001',
                'name' => 'System Derived Project',
                'location' => 'Rural Health Unit Compound',
                'new_contractor_name' => 'System QA Contractor',
                'startDate' => '2026-01-01',
                'endDate' => '2026-01-10',
                'phase' => 'Planning',
                'status' => 'completed',
                'progress' => 90,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'planning')
            ->assertJsonPath('data.progress', 0)
            ->assertJsonPath('data.duration_days', 10);

        $project = Project::where('project_code', 'SYSTEM-001')->firstOrFail();
        $this->assertEquals('planning', $project->status);
        $this->assertEquals(0.0, (float) $project->progress_percent);
    }

    public function test_admin_can_update_project()
    {
        $project = Project::create([
            'project_code' => 'UPDATE-001',
            'project_name' => 'Update Test',
            'location' => 'Municipal Hall Compound',
            'phase' => 'Planning',
            'status' => 'planning',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->patchJson("/api/v2/admin/projects/{$project->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_can_archive_project()
    {
        $project = Project::create([
            'project_code' => 'ARCHIVE-001',
            'project_name' => 'Archive Test',
            'location' => 'Municipal Hall Compound',
            'phase' => 'Planning',
            'status' => 'planning',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/v2/admin/projects/{$project->id}");

        $response->assertStatus(200);
        $this->assertTrue($project->fresh()->is_archived);
    }
}
