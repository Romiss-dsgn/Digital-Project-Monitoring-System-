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

        $contractors = [
            ['company_name' => 'Cagayan Valley Builders Corp.', 'contact_person' => 'Engr. Ramon Santos', 'license_number' => 'PCAB-CVB-2026-001'],
            ['company_name' => 'Northern Luzon Construction Services', 'contact_person' => 'Maria Villanueva', 'license_number' => 'PCAB-NLCS-2026-002'],
            ['company_name' => 'Red Shield Engineering Works', 'contact_person' => 'Carlo Mendoza', 'license_number' => 'PCAB-RSEW-2026-003'],
            ['company_name' => 'Tuguegarao Infrastructure Group', 'contact_person' => 'Ana Reyes', 'license_number' => 'PCAB-TIG-2026-004'],
            ['company_name' => 'Valley Fire Facilities Contractor', 'contact_person' => 'Jose Ramirez', 'license_number' => 'PCAB-VFFC-2026-005'],
        ];

        $contractorIds = collect();

        foreach ($contractors as $contractor) {
            $record = Contractor::updateOrCreate(
                ['license_number' => $contractor['license_number']],
                $contractor + [
                    'contact_number' => '09170000000',
                    'email' => strtolower(str_replace(' ', '.', $contractor['contact_person'])) . '@contractor.test',
                    'address' => 'Region II, Philippines',
                    'is_active' => true,
                ]
            );

            $contractorIds->push($record->id);
        }

        $projects = [
            ['project_code' => 'BFP-R2-PROJ-001', 'project_name' => 'Fire Station Construction - Tuguegarao City', 'location' => 'Tuguegarao City', 'approved_budget' => 18500000],
            ['project_code' => 'BFP-R2-PROJ-002', 'project_name' => 'Regional Office Renovation', 'location' => 'Cagayan', 'approved_budget' => 7200000],
            ['project_code' => 'BFP-R2-PROJ-003', 'project_name' => 'Fire Truck Bay Expansion', 'location' => 'Isabela', 'approved_budget' => 9800000],
            ['project_code' => 'BFP-R2-PROJ-004', 'project_name' => 'Emergency Operations Facility Upgrade', 'location' => 'Nueva Vizcaya', 'approved_budget' => 12600000],
            ['project_code' => 'BFP-R2-PROJ-005', 'project_name' => 'Dormitory and Training Hall Improvement', 'location' => 'Quirino', 'approved_budget' => 6400000],
        ];

        $projectIds = collect();

        foreach ($projects as $project) {
            $record = Project::updateOrCreate(
                ['project_code' => $project['project_code']],
                $project + [
                    'description' => 'Seeded project record for Contract Management MVP.',
                    'project_type' => 'Infrastructure',
                    'funding_source' => 'FY 2024 BFP Regional Allocation',
                    'phase' => 'Execution',
                    'status' => 'Ongoing',
                    'progress_percent' => 45,
                    'target_start_date' => Carbon::now()->subMonths(2),
                    'target_end_date' => Carbon::now()->addMonths(5),
                    'implementing_office' => 'BFP Region II',
                    'created_by' => $admin?->id,
                    'is_archived' => false,
                ]
            );

            $projectIds->push($record->id);
        }

        $contracts = [
            ['BFP-R2-CON-2024-001', 'Construction Contract - Tuguegarao Fire Station', 18500000, 'Active'],
            ['BFP-R2-CON-2024-002', 'Renovation Contract - Regional Office', 7200000, 'Active'],
            ['BFP-R2-CON-2024-003', 'Expansion Contract - Fire Truck Bay', 9800000, 'Pending Review'],
            ['BFP-R2-CON-2024-004', 'Facility Upgrade Contract', 12600000, 'Active'],
            ['BFP-R2-CON-2024-005', 'Training Hall Improvement Contract', 6400000, 'Draft'],
            ['BFP-R2-CON-2024-006', 'Electrical Systems Upgrade', 4300000, 'Completed'],
            ['BFP-R2-CON-2024-007', 'Plumbing and Sanitation Works', 3100000, 'Active'],
            ['BFP-R2-CON-2024-008', 'Site Development Works', 8800000, 'Delayed'],
            ['BFP-R2-CON-2024-009', 'Architectural Finishing Works', 5600000, 'Pending Review'],
            ['BFP-R2-CON-2024-010', 'Structural Repair Package', 6900000, 'Active'],
        ];

        foreach ($contracts as $index => [$number, $title, $amount, $status]) {
            Contract::updateOrCreate(
                ['contract_number' => $number],
                [
                    'project_id' => $projectIds[$index % $projectIds->count()],
                    'contractor_id' => $contractorIds[$index % $contractorIds->count()],
                    'contract_title' => $title,
                    'contract_type' => 'Infrastructure Works',
                    'original_contract_amount' => $amount,
                    'revised_contract_amount' => $amount,
                    'start_date' => Carbon::now()->subDays(45 + ($index * 5)),
                    'end_date' => Carbon::now()->addDays(120 + ($index * 10)),
                    'notice_to_proceed_date' => Carbon::now()->subDays(50 + ($index * 5)),
                    'signed_date' => Carbon::now()->subDays(55 + ($index * 5)),
                    'duration_days' => 180,
                    'status' => $status,
                    'remarks' => 'Seeded contract record for Contract Management MVP.',
                    'created_by' => $admin?->id,
                    'is_archived' => false,
                ]
            );
        }
    }
}
