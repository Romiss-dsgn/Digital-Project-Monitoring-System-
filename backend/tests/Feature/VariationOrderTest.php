<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\VariationOrder;
use App\Models\VariationOrderItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class VariationOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_approving_variation_order_updates_contract_revised_amount(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create', 'approve']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $vo = VariationOrder::create([
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Additional works',
            'reason' => 'Change order',
            'amount_change' => 150000.00,
            'time_impact_days' => 0,
            'status' => 'Submitted',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/review', [
            'review_action' => 'Approved',
            'approval_remarks' => 'Approved',
        ])->assertOk();

        $contract->refresh();
        $this->assertEquals(
            500000.00 + 150000.00,
            (float) $contract->revised_contract_amount
        );
    }

    public function test_approving_same_variation_order_twice_is_rejected(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create', 'approve']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $vo = VariationOrder::create([
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Additional works',
            'reason' => 'Change order',
            'amount_change' => 100000.00,
            'time_impact_days' => 0,
            'status' => 'Submitted',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/review', [
            'review_action' => 'Approved',
            'approval_remarks' => 'First approval',
        ])->assertOk();

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/review', [
            'review_action' => 'Approved',
            'approval_remarks' => 'Second approval',
        ])->assertStatus(422);

        $contract->refresh();
        $this->assertEquals(600000.00, (float) $contract->revised_contract_amount);
    }

    public function test_creating_variation_order_as_draft_does_not_set_submitted_metadata(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $response = $this->postJson('/api/v2/variation-orders', [
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Draft VO',
            'reason' => 'Draft reason',
            'amount_change' => 50000.00,
            'time_impact_days' => 0,
            'status' => 'Draft',
        ]);

        $response->assertCreated();

        $vo = VariationOrder::where('vo_number', $response->json('data.vo_number'))->first();
        $this->assertNull($vo->submitted_by);
        $this->assertNull($vo->submitted_at);
    }

    public function test_creating_variation_order_with_items_persists_the_worksheet_rows(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $response = $this->postJson('/api/v2/variation-orders', [
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Itemized VO',
            'reason' => 'Worksheet breakdown',
            'time_impact_days' => 4,
            'items' => [
                [
                    'line_number' => 1,
                    'item_description' => 'Additional site works',
                    'original_qty' => 10,
                    'original_unit' => 'm2',
                    'original_unit_cost' => 500,
                    'additive_qty' => 1,
                    'additive_unit' => 'lot',
                    'additive_unit_cost' => 2500,
                ],
                [
                    'line_number' => 2,
                    'item_description' => 'Deduct concrete cutting',
                    'deductive_qty' => 1,
                    'deductive_unit' => 'lot',
                    'deductive_unit_cost' => 1000,
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertEquals(1500.00, (float) $response->json('data.amount_change'));
        $this->assertCount(2, $response->json('data.items'));

        $vo = VariationOrder::where('vo_number', $response->json('data.vo_number'))->firstOrFail();
        $this->assertEquals(1500.00, (float) $vo->amount_change);
        $this->assertCount(2, $vo->items()->get());

        $item = VariationOrderItem::where('variation_order_id', $vo->id)->orderBy('line_number')->first();
        $this->assertEquals('Additional site works', $item->item_description);
        $this->assertEquals(2500.00, (float) $item->additive_total_cost);
    }

    public function test_reviewing_variation_order_twice_does_not_overwrite_reviewed_at(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create', 'approve']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $vo = VariationOrder::create([
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Review twice',
            'reason' => 'Test',
            'amount_change' => 10000.00,
            'time_impact_days' => 0,
            'status' => 'Submitted',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/review', [
            'review_action' => 'Under Review',
        ])->assertOk();

        $vo->refresh();
        $firstReviewAt = $vo->reviewed_at->format('Y-m-d H:i:s');

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/review', [
            'review_action' => 'Approved',
            'approval_remarks' => 'Approved',
        ])->assertOk();

        $vo->refresh();
        $this->assertEquals($firstReviewAt, $vo->reviewed_at->format('Y-m-d H:i:s'));
    }

    public function test_update_endpoint_ignores_status_field(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create', 'edit']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $vo = VariationOrder::create([
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Draft VO',
            'reason' => 'Draft',
            'amount_change' => 10000.00,
            'time_impact_days' => 0,
            'status' => 'Draft',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/variation-orders/' . $vo->id, [
            'description' => 'Updated description',
            'status' => 'Approved',
        ])->assertOk();

        $vo->refresh();
        $this->assertEquals('Draft', $vo->status);
        $this->assertEquals('Updated description', $vo->description);
        $this->assertNull($vo->approved_by);
        $this->assertNull($vo->approved_at);
    }

    public function test_submit_endpoint_accepts_a_draft_variation_order(): void
    {
        $user = $this->userWithPermissions('variation_orders', ['view', 'create']);
        Passport::actingAs($user);
        [$project, $contractor, $contract] = $this->projectAndContractorAndContract();

        $vo = VariationOrder::create([
            'contract_id' => $contract->id,
            'vo_number' => 'VO-' . uniqid(),
            'description' => 'Draft VO',
            'reason' => 'Draft',
            'amount_change' => 10000.00,
            'time_impact_days' => 0,
            'status' => 'Draft',
            'is_archived' => false,
        ]);

        $this->patchJson('/api/v2/variation-orders/' . $vo->id . '/submit')
            ->assertOk();

        $vo->refresh();
        $this->assertEquals('Submitted', $vo->status);
        $this->assertEquals($user->id, $vo->submitted_by);
        $this->assertNotNull($vo->submitted_at);
    }

    private function userWithPermissions(string $module, array $actions): User
    {
        $role = Role::create(['name' => 'Test ' . $module . ' ' . uniqid(), 'description' => 'Feature test role']);
        $values = ['role_id' => $role->id, 'module' => $module];

        foreach (['view', 'create', 'edit', 'delete', 'approve', 'export'] as $action) {
            $values['can_' . $action] = in_array($action, $actions, true);
        }

        RolePermission::create($values);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function projectAndContractorAndContract(): array
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

        $contract = Contract::create([
            'contract_number' => 'LGU-TUAO-CON-2026-' . random_int(100000, 999999),
            'contract_title' => 'Feature Test Contract',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
            'contract_type' => 'Infrastructure Works',
            'original_contract_amount' => 500000.00,
            'revised_contract_amount' => 500000.00,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Draft',
            'is_archived' => false,
        ]);

        return [$project, $contractor, $contract];
    }
}
