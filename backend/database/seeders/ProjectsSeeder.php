<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first();

        $contractors = [
            [
                'company_name' => 'Cagayan Valley Builders Corp.',
                'contact_person' => 'Engr. Ramon Santos',
                'contact_number' => '09170000001',
                'email' => 'cagayan.builders@contractor.test',
                'license_number' => 'PCAB-QA-2026-001',
            ],
            [
                'company_name' => 'Northern Luzon Construction Services',
                'contact_person' => 'Maria Villanueva',
                'contact_number' => '09170000002',
                'email' => 'northern.luzon@contractor.test',
                'license_number' => 'PCAB-QA-2026-002',
            ],
            [
                'company_name' => 'Red Shield Engineering Works',
                'contact_person' => 'Carlo Mendoza',
                'contact_number' => '09170000003',
                'email' => 'red.shield@contractor.test',
                'license_number' => 'PCAB-QA-2026-003',
            ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::updateOrCreate(
                ['company_name' => $contractor['company_name']],
                $contractor + [
                    'address' => 'Region II, Philippines',
                    'is_active' => true,
                ]
            );
        }

        $contractorIds = Contractor::query()
            ->whereIn('company_name', collect($contractors)->pluck('company_name'))
            ->pluck('id', 'company_name');

        $projects = [
            [
                'project_code' => 'BFP-R2-PROJ-001',
                'project_name' => 'Tuguegarao Central Fire Station Phase II',
                'location' => 'Cagayan',
                'contractor' => 'Cagayan Valley Builders Corp.',
                'phase' => 'Construction',
                'status' => 'on_time',
                'progress_percent' => 65,
                'approved_budget' => 18500000,
                'target_start_date' => '2026-01-15',
                'target_end_date' => '2026-10-30',
                'description' => 'QA seed project for project planning, contracts, and dashboard totals.',
            ],
            [
                'project_code' => 'BFP-R2-PROJ-002',
                'project_name' => 'Regional Office Records Room Renovation',
                'location' => 'Cagayan',
                'contractor' => 'Northern Luzon Construction Services',
                'phase' => 'Procurement',
                'status' => 'ongoing',
                'progress_percent' => 35,
                'approved_budget' => 7200000,
                'target_start_date' => '2026-02-01',
                'target_end_date' => '2026-08-31',
                'description' => 'QA seed project for engineering plan upload and contract review.',
            ],
            [
                'project_code' => 'BFP-R2-PROJ-003',
                'project_name' => 'Ilagan Fire Truck Bay Expansion',
                'location' => 'Isabela',
                'contractor' => 'Red Shield Engineering Works',
                'phase' => 'Execution',
                'status' => 'delayed',
                'progress_percent' => 45,
                'approved_budget' => 9800000,
                'target_start_date' => '2026-03-01',
                'target_end_date' => '2026-09-15',
                'description' => 'QA seed project with a delayed status for alerts and report testing.',
            ],
        ];

        foreach ($projects as $project) {
            $contractorName = $project['contractor'];

            Project::updateOrCreate(
                ['project_code' => $project['project_code']],
                [
                    'project_name' => $project['project_name'],
                    'location' => $project['location'],
                    'contractor_id' => $contractorIds[$contractorName] ?? null,
                    'implementing_office' => $contractorName,
                    'project_type' => 'Infrastructure',
                    'funding_source' => 'FY 2026 BFP Regional Allocation',
                    'phase' => $project['phase'],
                    'status' => $project['status'],
                    'progress_percent' => $project['progress_percent'],
                    'approved_budget' => $project['approved_budget'],
                    'target_start_date' => $project['target_start_date'],
                    'target_end_date' => $project['target_end_date'],
                    'description' => $project['description'],
                    'created_by' => $admin?->id,
                    'is_archived' => false,
                ]
            );
        }
    }
}
