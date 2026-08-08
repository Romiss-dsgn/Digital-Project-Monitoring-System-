<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContractManagementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first();

        $contracts = [
            [
                'contract_number' => 'LGU-TUAO-CON-2026-001',
                'project_code' => 'LGU-TUAO-PROJ-001',
                'contractor_name' => 'Tuao Builders and Supply Corp.',
                'contract_title' => 'Improvement Contract - Municipal Hall Records Room',
                'amount' => 18500000,
                'status' => 'Active',
                'start_offset_days' => -120,
                'end_offset_days' => 180,
            ],
            [
                'contract_number' => 'LGU-TUAO-CON-2026-002',
                'project_code' => 'LGU-TUAO-PROJ-002',
                'contractor_name' => 'Tuao Drainage and Roadworks Services',
                'contract_title' => 'Rehabilitation Contract - Public Market Drainage',
                'amount' => 7200000,
                'status' => 'Pending Review',
                'start_offset_days' => -45,
                'end_offset_days' => 150,
            ],
            [
                'contract_number' => 'LGU-TUAO-CON-2026-003',
                'project_code' => 'LGU-TUAO-PROJ-003',
                'contractor_name' => 'Tuao Civil Works and Engineering',
                'contract_title' => 'Site Development Contract - Rural Health Unit Compound',
                'amount' => 9800000,
                'status' => 'Draft',
                'start_offset_days' => 15,
                'end_offset_days' => 210,
            ],
        ];

        foreach ($contracts as $contract) {
            $project = Project::where('project_code', $contract['project_code'])->first();
            $contractor = Contractor::where('company_name', $contract['contractor_name'])->first();
            $startDate = Carbon::today()->addDays($contract['start_offset_days']);
            $endDate = Carbon::today()->addDays($contract['end_offset_days']);

            if (! $project || ! $contractor) {
                continue;
            }

            Contract::updateOrCreate(
                ['contract_number' => $contract['contract_number']],
                [
                    'project_id' => $project->id,
                    'contractor_id' => $contractor->id,
                    'contract_title' => $contract['contract_title'],
                    'contract_type' => 'Infrastructure Works',
                    'original_contract_amount' => $contract['amount'],
                    'revised_contract_amount' => $contract['amount'],
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'notice_to_proceed_date' => $startDate->copy()->subDays(5)->toDateString(),
                    'signed_date' => $startDate->copy()->subDays(10)->toDateString(),
                    'duration_days' => $startDate->diffInDays($endDate) + 1,
                    'status' => $contract['status'],
                    'remarks' => 'QA seed contract for end-to-end contract monitoring tests.',
                    'created_by' => $admin?->id,
                    'approved_by' => $contract['status'] === 'Active' ? $admin?->id : null,
                    'approved_at' => $contract['status'] === 'Active' ? now() : null,
                    'is_archived' => false,
                ]
            );
        }
    }
}
