<?php

namespace Tests\Feature;

use App\Models\CashflowPeriod;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CashflowPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashflow_period_actuals_are_recalculated_from_payments_and_invoice_status_changes(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create', 'delete'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(9000.00);

        $response = $this->postJson('/api/v2/cashflow-periods', [
            'contract_id' => $contract->id,
            'period_label' => 'Q1 Cashflow',
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'planned_amount' => 1000.00,
        ]);

        $response->assertCreated();

        $period = CashflowPeriod::findOrFail($response->json('data.id'));
        $this->assertEquals(1000.00, (float) $period->planned_amount);
        $this->assertEquals(0.00, (float) $period->actual_amount);
        $this->assertEquals(1000.00, (float) $period->variance);

        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'cashflow_period_id' => $period->id,
            'invoice_number' => 'INV-' . uniqid(),
            'billing_period' => 'January 2026',
            'invoice_amount' => 1500.00,
            'invoice_date' => '2026-01-15',
            'due_date' => '2026-01-30',
            'status' => 'Pending',
        ]);

        $period->refresh();
        $this->assertEquals(0.00, (float) $period->actual_amount);
        $this->assertEquals(1000.00, (float) $period->variance);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount_paid' => 250.75,
            'payment_date' => '2026-01-20',
            'payment_method' => 'Bank Transfer',
            'recorded_by' => $user->id,
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount_paid' => 100.25,
            'payment_date' => '2026-01-22',
            'payment_method' => 'Bank Transfer',
            'recorded_by' => $user->id,
        ]);

        $period->refresh();
        $this->assertEquals(351.00, (float) $period->actual_amount);
        $this->assertEquals(649.00, (float) $period->variance);

        $invoice->update(['status' => 'Paid']);

        $period->refresh();
        $this->assertEquals(351.00, (float) $period->actual_amount);
        $this->assertEquals(649.00, (float) $period->variance);
    }

    public function test_cashflow_summary_includes_revised_contract_amount_and_ignores_archived_periods(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create', 'delete'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(9000.00);

        $activePeriod = $this->createCashflowPeriod($contract->id, 'Active Period', 1000.00);
        $archivedPeriod = $this->createCashflowPeriod($contract->id, 'Archived Period', 500.00);

        $this->deleteJson('/api/v2/cashflow-periods/' . $archivedPeriod->id)->assertOk();

        $summary = $this->getJson('/api/v2/cashflow-periods/summary')->assertOk();

        $this->assertEquals(1000.00, (float) $summary->json('data.planned_total'));
        $this->assertEquals(0.00, (float) $summary->json('data.actual_total'));
        $this->assertEquals(9000.00, (float) $summary->json('data.revised_contract_amount'));

        $this->assertDatabaseHas('cashflow_periods', [
            'id' => $archivedPeriod->id,
            'is_archived' => true,
        ]);
        $this->assertDatabaseHas('cashflow_periods', [
            'id' => $activePeriod->id,
            'is_archived' => false,
        ]);
    }

    public function test_cashflow_period_destroy_archives_record_instead_of_deleting(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create', 'delete'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(5000.00);

        $period = $this->createCashflowPeriod($contract->id, 'Archive Test', 750.00);

        $this->deleteJson('/api/v2/cashflow-periods/' . $period->id)->assertOk();

        $period->refresh();
        $this->assertTrue($period->is_archived);
        $this->assertDatabaseHas('cashflow_periods', [
            'id' => $period->id,
            'is_archived' => true,
        ]);
    }

    public function test_payment_created_through_disbursement_marks_invoice_paid_when_fully_covered(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create'],
            'invoices' => ['view', 'create'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(8000.00);

        $period = $this->createCashflowPeriod($contract->id, 'Payment Test', 1200.00);

        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'cashflow_period_id' => $period->id,
            'invoice_number' => 'INV-' . uniqid(),
            'billing_period' => 'February 2026',
            'invoice_amount' => 450.00,
            'invoice_date' => '2026-02-10',
            'due_date' => '2026-02-25',
            'status' => 'Approved',
        ]);

        $response = $this->postJson('/api/v2/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 450.00,
            'payment_date' => '2026-02-18',
            'payment_method' => 'Check',
            'remarks' => 'Manual payment entry',
        ]);

        $response->assertCreated();
        $this->assertEquals(450.00, (float) $response->json('data.amount_paid'));

        $invoice->refresh();
        $period->refresh();

        $this->assertSame('Paid', $invoice->status);
        $this->assertEquals(450.00, (float) $period->actual_amount);
        $this->assertEquals(750.00, (float) $period->variance);

        $summary = $this->getJson('/api/v2/cashflow-periods/summary')->assertOk();
        $this->assertEquals(450.00, (float) $summary->json('data.actual_total'));
    }

    public function test_partial_payment_keeps_invoice_open_and_exposes_remaining_balance(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create'],
            'invoices' => ['view', 'create'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(6000.00);

        $period = $this->createCashflowPeriod($contract->id, 'Partial Payment Test', 1000.00);

        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'cashflow_period_id' => $period->id,
            'invoice_number' => 'INV-' . uniqid(),
            'billing_period' => 'March 2026',
            'invoice_amount' => 500.00,
            'invoice_date' => '2026-03-01',
            'due_date' => '2026-03-15',
            'status' => 'Approved',
        ]);

        $response = $this->postJson('/api/v2/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 200.00,
            'payment_date' => '2026-03-05',
            'payment_method' => 'Bank Transfer',
            'remarks' => 'Partial payment',
        ]);

        $response->assertCreated();
        $this->assertEquals(200.00, (float) $response->json('data.amount_paid'));

        $invoiceResponse = $this->getJson('/api/v2/invoices/' . $invoice->id);
        $invoiceResponse->assertOk();
        $this->assertEquals(200.00, (float) $invoiceResponse->json('data.paid_amount'));
        $this->assertEquals(300.00, (float) $invoiceResponse->json('data.remaining_balance'));

        $invoice->refresh();
        $period->refresh();

        $this->assertSame('Approved', $invoice->status);
        $this->assertEquals(200.00, (float) $period->actual_amount);
        $this->assertEquals(800.00, (float) $period->variance);

        $summary = $this->getJson('/api/v2/cashflow-periods/summary')->assertOk();
        $this->assertEquals(200.00, (float) $summary->json('data.actual_total'));
    }

    public function test_overpayment_is_rejected_before_creating_a_payment(): void
    {
        $user = $this->userWithPermissions([
            'cashflow_periods' => ['view', 'create'],
            'invoices' => ['view', 'create'],
        ]);
        Passport::actingAs($user);
        [, , $contract] = $this->projectAndContractorAndContract(5000.00);

        $period = $this->createCashflowPeriod($contract->id, 'Overpayment Test', 1000.00);

        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'cashflow_period_id' => $period->id,
            'invoice_number' => 'INV-' . uniqid(),
            'billing_period' => 'April 2026',
            'invoice_amount' => 250.00,
            'invoice_date' => '2026-04-01',
            'due_date' => '2026-04-20',
            'status' => 'Approved',
        ]);

        $this->postJson('/api/v2/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 300.00,
            'payment_date' => '2026-04-05',
            'payment_method' => 'Cash',
            'remarks' => 'Should fail',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('payments', [
            'invoice_id' => $invoice->id,
            'amount_paid' => '300.00',
        ]);
    }

    private function createCashflowPeriod(int $contractId, string $label, float $plannedAmount): CashflowPeriod
    {
        $response = $this->postJson('/api/v2/cashflow-periods', [
            'contract_id' => $contractId,
            'period_label' => $label,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'planned_amount' => $plannedAmount,
        ]);

        $response->assertCreated();

        return CashflowPeriod::findOrFail($response->json('data.id'));
    }

    private function userWithPermissions(array $modulePermissions): User
    {
        $role = Role::create([
            'name' => 'Test Role ' . uniqid(),
            'description' => 'Feature test role',
        ]);

        foreach ($modulePermissions as $module => $actions) {
            $values = ['role_id' => $role->id, 'module' => $module];

            foreach (['view', 'create', 'edit', 'delete', 'approve', 'export'] as $action) {
                $values['can_' . $action] = in_array($action, $actions, true);
            }

            RolePermission::create($values);
        }

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function projectAndContractorAndContract(float $revisedAmount): array
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
            'original_contract_amount' => $revisedAmount,
            'revised_contract_amount' => $revisedAmount,
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-01',
            'status' => 'Draft',
            'is_archived' => false,
        ]);

        return [$project, $contractor, $contract];
    }
}
