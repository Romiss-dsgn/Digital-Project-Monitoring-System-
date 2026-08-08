<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ContractManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_user_can_create_contract_and_audit_record(): void
    {
        $user = $this->userWithPermissions('contracts', ['view', 'create', 'approve']);
        Passport::actingAs($user);
        [$project, $contractor] = $this->projectAndContractor();

        $response = $this->postJson('/api/v2/contracts', [
            'contract_number' => 'LGU-TUAO-CON-2026-901',
            'contract_title' => 'Feature Test Contract',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 1250000.50,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Draft',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.contract_number', 'LGU-TUAO-CON-2026-901')
            ->assertJsonPath('data.duration_days', 184);
        $this->assertDatabaseHas('contracts', ['contract_number' => 'LGU-TUAO-CON-2026-901']);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'contracts',
            'action' => 'created',
            'record_code' => 'LGU-TUAO-CON-2026-901',
        ]);
    }

    public function test_contract_validation_rejects_invalid_number_and_dates(): void
    {
        $user = $this->userWithPermissions('contracts', ['create']);
        Passport::actingAs($user);
        [$project, $contractor] = $this->projectAndContractor();

        $this->postJson('/api/v2/contracts', [
            'contract_number' => 'invalid',
            'contract_title' => 'Invalid Contract',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 1000,
            'start_date' => '2026-12-01',
            'end_date' => '2026-06-01',
            'status' => 'Draft',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['contract_number', 'end_date']);
    }

    public function test_contract_document_is_stored_privately(): void
    {
        Storage::fake('local');
        $user = $this->userWithPermissions('contract_documents', ['create']);
        Passport::actingAs($user);
        [$project, $contractor] = $this->projectAndContractor();
        $contract = Contract::create([
            'contract_number' => 'LGU-TUAO-CON-2026-902',
            'contract_title' => 'Document Test Contract',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 1000,
            'revised_contract_amount' => 1000,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Draft',
        ]);

        $response = $this->post('/api/v2/contracts/' . $contract->id . '/documents', [
            'files' => [UploadedFile::fake()->create('notice.pdf', 100, 'application/pdf')],
            'document_category' => 'Contract Document',
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $path = $response->json('data.0') ? $contract->documents()->first()->file_path : null;
        Storage::disk('local')->assertExists($path);
    }

    public function test_authorized_user_can_update_and_archive_contract(): void
    {
        $user = $this->userWithPermissions('contracts', ['edit', 'delete', 'approve']);
        Passport::actingAs($user);
        [$project, $contractor] = $this->projectAndContractor();
        $contract = Contract::create([
            'contract_number' => 'LGU-TUAO-CON-2026-903',
            'contract_title' => 'Workflow Test Contract',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 5000,
            'revised_contract_amount' => 5000,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Pending Review',
        ]);

        $this->patchJson('/api/v2/contracts/' . $contract->id, [
            'contract_number' => $contract->contract_number,
            'contract_title' => $contract->contract_title,
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => $contract->contract_type,
            'original_contract_amount' => 5000,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Active',
        ])->assertOk()->assertJsonPath('data.status', 'Active');

        $this->deleteJson('/api/v2/contracts/' . $contract->id)->assertOk();
        $this->assertTrue($contract->fresh()->is_archived);
        $this->assertDatabaseHas('audit_logs', ['module' => 'contracts', 'action' => 'archived']);
    }

    public function test_user_without_contract_permission_is_forbidden(): void
    {
        $role = Role::create(['name' => 'No Contract Access', 'description' => 'Test role']);
        $user = User::factory()->create(['role_id' => $role->id]);
        Passport::actingAs($user);

        $this->getJson('/api/v2/contracts')->assertForbidden();
    }

    private function userWithPermissions(string $module, array $actions): User
    {
        $role = Role::create(['name' => "Test {$module} " . uniqid(), 'description' => 'Feature test role']);
        $values = ['role_id' => $role->id, 'module' => $module];

        foreach (['view', 'create', 'edit', 'delete', 'approve', 'export'] as $action) {
            $values['can_' . $action] = in_array($action, $actions, true);
        }

        RolePermission::create($values);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function projectAndContractor(): array
    {
        $project = Project::create([
            'project_code' => 'TEST-PROJ-' . uniqid(),
            'project_name' => 'Feature Test Project',
            'status' => 'ongoing',
            'is_archived' => false,
        ]);
        $contractor = Contractor::create([
            'company_name' => 'Feature Test Contractor ' . uniqid(),
            'license_number' => 'TEST-' . uniqid(),
            'is_active' => true,
        ]);

        return [$project, $contractor];
    }
}
