<?php

namespace Database\Seeders;

use App\Models\EngineeringPlan;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EngineeringPlansSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first();

        $projects = Project::query()
            ->whereIn('project_code', [
                'BFP-R2-PROJ-001',
                'BFP-R2-PROJ-002',
                'BFP-R2-PROJ-003',
                'BFP-R2-PROJ-004',
                'BFP-R2-PROJ-005',
            ])
            ->get()
            ->keyBy('project_code');

        $plans = [
            [
                'project_code' => 'BFP-R2-PROJ-001',
                'plan_title' => 'Main Building Structural Revision 2',
                'plan_type' => 'Structural',
                'version' => 'v2.4',
                'file_name' => 'Main_Bldg_A_Structural_Rev2.pdf',
                'file_type' => 'PDF',
                'status' => EngineeringPlan::STATUS_APPROVED,
                'uploaded_at' => Carbon::parse('2026-06-01 09:45:00'),
                'remarks' => null,
            ],
            [
                'project_code' => 'BFP-R2-PROJ-002',
                'plan_title' => 'Regional Office Electrical Layout',
                'plan_type' => 'Electrical',
                'version' => 'v1.1',
                'file_name' => 'Fire_Station_B_Electrical_Layout.dwg',
                'file_type' => 'DWG',
                'status' => EngineeringPlan::STATUS_FOR_REVIEW,
                'uploaded_at' => Carbon::parse('2026-06-03 14:15:00'),
                'remarks' => 'Awaiting electrical compliance check',
            ],
            [
                'project_code' => 'BFP-R2-PROJ-003',
                'plan_title' => 'Mechanical Specifications Revision 2',
                'plan_type' => 'Mechanical',
                'version' => 'v2.0',
                'file_name' => 'San_Mateo_Mechanical_Spec_V2.png',
                'file_type' => 'PNG',
                'status' => EngineeringPlan::STATUS_REVISION,
                'uploaded_at' => Carbon::parse('2026-06-05 11:20:00'),
                'remarks' => 'Revise ventilation notes before final approval',
            ],
            [
                'project_code' => 'BFP-R2-PROJ-004',
                'plan_title' => 'HVAC Ventilation Schematic 2026',
                'plan_type' => 'Mechanical',
                'version' => 'v1.0',
                'file_name' => 'HVAC_Ventilation_Schema_2026.docx',
                'file_type' => 'DOCX',
                'status' => EngineeringPlan::STATUS_UPLOADED,
                'uploaded_at' => Carbon::parse('2026-06-07 16:30:00'),
                'remarks' => null,
            ],
            [
                'project_code' => 'BFP-R2-PROJ-005',
                'plan_title' => 'Training Hall Architectural Plan',
                'plan_type' => 'Architectural',
                'version' => 'v1.3',
                'file_name' => 'Training_Hall_Architectural_Plan.pdf',
                'file_type' => 'PDF',
                'status' => EngineeringPlan::STATUS_APPROVED,
                'uploaded_at' => Carbon::parse('2026-06-09 10:00:00'),
                'remarks' => null,
            ],
        ];

        foreach ($plans as $plan) {
            $project = $projects->get($plan['project_code']);

            if (! $project) {
                continue;
            }

            // Seeded files are placeholders for the MVP register; real uploads use storage/app/public.
            EngineeringPlan::updateOrCreate(
                ['file_name' => $plan['file_name']],
                [
                    'project_id' => $project->id,
                    'plan_title' => $plan['plan_title'],
                    'plan_type' => $plan['plan_type'],
                    'version' => $plan['version'],
                    'file_path' => 'seeded/engineering-plans/' . $plan['file_name'],
                    'file_type' => $plan['file_type'],
                    'status' => $plan['status'],
                    'uploaded_by' => $admin?->id,
                    'uploaded_at' => $plan['uploaded_at'],
                    'remarks' => $plan['remarks'],
                    'is_archived' => false,
                ]
            );
        }
    }
}
