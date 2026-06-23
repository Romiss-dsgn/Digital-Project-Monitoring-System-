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

        $contractors = Contractor::insert([
            ['company_name' => 'V.G. Construction Services', 'contact_person' => 'Victor Garcia', 'contact_number' => '09123456789', 'email' => 'info@vgconstruction.com', 'is_active' => true],
            ['company_name' => 'BuildRight Partners Corp.', 'contact_person' => 'Betty Right', 'contact_number' => '09123456788', 'email' => 'contact@buildright.com', 'is_active' => true],
            ['company_name' => 'NorthEdge Engineering', 'contact_person' => 'Nelson Edge', 'contact_number' => '09123456787', 'email' => 'info@northedge.com', 'is_active' => true],
            ['company_name' => 'PrimeBuilders Inc.', 'contact_person' => 'Peter Builder', 'contact_number' => '09123456786', 'email' => 'info@primebuilders.com', 'is_active' => true],
            ['company_name' => 'EcoPower Solutions', 'contact_person' => 'Eco Green', 'contact_number' => '09123456785', 'email' => 'info@ecopower.com', 'is_active' => true],
        ]);

        $contractorIds = Contractor::pluck('id', 'company_name');

        $projects = [
            [
                'project_code' => 'BFP-2024-C001',
                'project_name' => 'Tuguegarao Central Fire Station - Phase II',
                'location' => 'Cagayan',
                'phase' => 'Construction',
                'status' => 'on_time',
                'progress_percent' => 45,
                'approved_budget' => 5000000.00,
                'target_start_date' => '2024-01-15',
                'target_end_date' => '2024-12-30',
            ],
            [
                'project_code' => 'BFP-2024-1012',
                'project_name' => 'Ilagan City Fire Sub-Station Annex',
                'location' => 'Isabela',
                'phase' => 'Foundation',
                'status' => 'delayed',
                'progress_percent' => 12,
                'approved_budget' => 3500000.00,
                'target_start_date' => '2024-03-01',
                'target_end_date' => '2024-10-15',
            ],
            [
                'project_code' => 'BFP-2023-N005',
                'project_name' => 'Bayombong Regional Logistics Hub',
                'location' => 'Nueva Vizcaya',
                'phase' => 'Finishing',
                'status' => 'ongoing',
                'progress_percent' => 85,
                'approved_budget' => 8750000.00,
                'target_start_date' => '2023-06-01',
                'target_end_date' => '2023-11-30',
            ],
            [
                'project_code' => 'BFP-2024-P002',
                'project_name' => 'Region II Headquarters Renovation',
                'location' => 'Cagayan',
                'phase' => 'Planning',
                'status' => 'planning',
                'progress_percent' => 5,
                'approved_budget' => 2200000.00,
                'target_start_date' => '2024-07-01',
                'target_end_date' => '2025-03-30',
            ],
            [
                'project_code' => 'BFP-2023-Q008',
                'project_name' => 'Quirino Provincial Fire Office - Solar Project',
                'location' => 'Quirino',
                'phase' => 'Post-Eval',
                'status' => 'completed',
                'progress_percent' => 100,
                'approved_budget' => 1500000.00,
                'target_start_date' => '2023-01-10',
                'target_end_date' => '2023-12-15',
                'actual_end_date' => '2023-12-10',
            ],
        ];

        foreach ($projects as $data) {
            Project::updateOrCreate(
                ['project_code' => $data['project_code']],
                $data + ['created_by' => $admin?->id]
            );
        }
    }
}