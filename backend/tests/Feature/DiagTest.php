<?php

namespace Tests\Feature;

use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DiagTest extends TestCase
{
    use DatabaseTransactions;

    public function test_diag_fresh(): void
    {
        $role = Role::create(['name' => 'Diag ' . uniqid(), 'description' => 'd']);
        RolePermission::create(['role_id' => $role->id, 'module' => 'cashflow_periods',
            'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true,
            'can_approve' => true, 'can_export' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $project = Project::create(['project_code' => 'DIA-' . uniqid(), 'project_name' => 'd', 'status' => 'ongoing', 'is_archived' => false]);
        $contractor = Contractor::create(['company_name' => 'DIA ' . uniqid(), 'license_number' => 'D-' . uniqid(), 'is_active' => true]);
        $contract = Contract::create([
            'contract_number' => 'DIA-' . uniqid(), 'contract_title' => 'd', 'project_id' => $project->id,
            'contractor_id' => $contractor->id, 'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 1000, 'revised_contract_amount' => 1000,
            'start_date' => '2026-06-01', 'end_date' => '2026-12-01', 'status' => 'Draft', 'is_archived' => false,
        ]);

        $p = CashflowPeriod::create([
            'contract_id' => $contract->id, 'period_label' => 'D', 'planned_amount' => 750.00,
            'created_by' => $user->id, 'status' => 'On Track', 'is_archived' => false,
        ]);

        $p->update(['is_archived' => true]);
        fwrite(STDERR, "\nFRESH_NULL=" . (is_null($p->fresh()) ? 'YES' : 'NO'));
        fwrite(STDERR, "\nWITHOUT_SCOPE_NULL=" . (is_null(CashflowPeriod::withoutGlobalScopes()->find($p->id)) ? 'YES' : 'NO'));
        fwrite(STDERR, "\nGLOBAL_SCOPES=" . count($p->getGlobalScopes() ?: []) . "\n");

        $this->assertTrue(true);
    }
}
