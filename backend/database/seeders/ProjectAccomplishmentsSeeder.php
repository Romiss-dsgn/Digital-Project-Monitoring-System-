<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectAccomplishmentsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first();

        $milestones = [
            [
                'project_code' => 'BFP-R2-PROJ-001',
                'milestone_title' => 'Structural Works Completion',
                'percent_complete' => 75,
                'status' => 'In Progress',
                'target_offset_days' => 45,
                'completion_offset_days' => null,
                'remarks' => null,
            ],
            [
                'project_code' => 'BFP-R2-PROJ-002',
                'milestone_title' => 'Procurement Documentation Review',
                'percent_complete' => 100,
                'status' => 'Completed',
                'target_offset_days' => -10,
                'completion_offset_days' => -12,
                'remarks' => 'Validated for QA reporting checks.',
            ],
            [
                'project_code' => 'BFP-R2-PROJ-003',
                'milestone_title' => 'Site Works Recovery Plan',
                'percent_complete' => 35,
                'status' => 'Delayed',
                'target_offset_days' => -5,
                'completion_offset_days' => null,
                'remarks' => 'Requires follow-up during E2E testing.',
            ],
        ];

        foreach ($milestones as $milestone) {
            $project = Project::where('project_code', $milestone['project_code'])->first();

            if (! $project) {
                continue;
            }

            ProjectAccomplishment::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'milestone_title' => $milestone['milestone_title'],
                ],
                [
                    'description' => 'QA seed accomplishment. Upload proof documents during E2E tests.',
                    'target_date' => Carbon::today()->addDays($milestone['target_offset_days']),
                    'completion_date' => $milestone['completion_offset_days'] !== null
                        ? Carbon::today()->addDays($milestone['completion_offset_days'])
                        : null,
                    'percent_complete' => $milestone['percent_complete'],
                    'status' => $milestone['status'],
                    'reported_by' => $admin?->id,
                    'validated_by' => $milestone['status'] === 'Completed' ? $admin?->id : null,
                    'validated_at' => $milestone['status'] === 'Completed' ? now() : null,
                    'remarks' => $milestone['remarks'],
                    'is_archived' => false,
                ]
            );

            $project->update(['progress_percent' => $milestone['percent_complete']]);
        }
    }
}
