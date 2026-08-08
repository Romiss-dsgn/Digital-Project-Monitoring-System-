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
                'company_name' => 'Tuao Builders and Supply Corp.',
                'contact_person' => 'Engr. Ramon Santos',
                'contact_number' => '09170000001',
                'email' => 'tuao.builders@contractor.test',
                'license_number' => 'PCAB-QA-2026-001',
            ],
            [
                'company_name' => 'Tuao Drainage and Roadworks Services',
                'contact_person' => 'Maria Villanueva',
                'contact_number' => '09170000002',
                'email' => 'tuao.roadworks@contractor.test',
                'license_number' => 'PCAB-QA-2026-002',
            ],
            [
                'company_name' => 'Tuao Civil Works and Engineering',
                'contact_person' => 'Carlo Mendoza',
                'contact_number' => '09170000003',
                'email' => 'tuao.civilworks@contractor.test',
                'license_number' => 'PCAB-QA-2026-003',
            ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::updateOrCreate(
                ['company_name' => $contractor['company_name']],
                $contractor + [
                    'address' => 'Tuao Municipal Proper',
                    'is_active' => true,
                ]
            );
        }

        $contractorIds = Contractor::query()
            ->whereIn('company_name', collect($contractors)->pluck('company_name'))
            ->pluck('id', 'company_name');

        $projects = [
            [
                'project_code' => 'LGU-TUAO-PROJ-001',
                'project_name' => 'Tuao Municipal Hall Records Room Improvement',
                'location' => 'Municipal Hall Compound',
                'contractor' => 'Tuao Builders and Supply Corp.',
                'phase' => 'Construction',
                'status' => 'on_time',
                'progress_percent' => 65,
                'approved_budget' => 18500000,
                'target_start_date' => '2026-01-15',
                'target_end_date' => '2026-10-30',
                'description' => 'QA seed project for project planning, contracts, and dashboard totals.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-002',
                'project_name' => 'Tuao Public Market Drainage Rehabilitation',
                'location' => 'Public Market Area',
                'contractor' => 'Tuao Drainage and Roadworks Services',
                'phase' => 'Post Evaluation',
                'status' => 'ongoing',
                'progress_percent' => 35,
                'approved_budget' => 7200000,
                'target_start_date' => '2026-02-01',
                'target_end_date' => '2026-08-31',
                'description' => 'QA seed project for engineering plan upload and contract review.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-003',
                'project_name' => 'Tuao Rural Health Unit Site Development',
                'location' => 'Rural Health Unit Compound',
                'contractor' => 'Tuao Civil Works and Engineering',
                'phase' => 'Construction',
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
                    'funding_source' => 'FY 2026 LGU Tuao Infrastructure Allocation',
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
