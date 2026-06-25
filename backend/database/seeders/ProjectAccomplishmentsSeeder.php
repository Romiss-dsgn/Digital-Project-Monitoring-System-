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
        $projects = Project::query()
            ->whereIn('project_code', [
                'BFP-R2-PROJ-001',
                'BFP-R2-PROJ-002',
                'BFP-R2-PROJ-003',
                'BFP-R2-PROJ-004',
                'BFP-R2-PROJ-005',
            ])
            ->orderBy('project_code')
            ->get();

        $milestones = [
            ['Site Preparation', 100, 'Completed', -40, -42],
            ['Foundation and Structural Works', 75, 'In Progress', 30, null],
            ['Electrical Installation', 45, 'In Progress', 50, null],
            ['Plumbing and Sanitation', 30, 'Delayed', -5, null],
            ['Roofing and Weatherproofing', 0, 'Not Started', 80, null],
            ['Interior Finishing', 90, 'In Progress', 14, null],
            ['Fire Safety Systems Installation', 60, 'In Progress', 35, null],
            ['Equipment Testing and Commissioning', 0, 'Not Started', 100, null],
            ['Final Inspection', 100, 'Completed', -10, -12],
            ['Project Turnover', 15, 'Delayed', -2, null],
        ];

        foreach ($milestones as $index => [$title, $percent, $status, $targetOffset, $completionOffset]) {
            $project = $projects[$index % max($projects->count(), 1)] ?? null;

            if (! $project) {
                continue;
            }

            ProjectAccomplishment::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'milestone_title' => $title,
                ],
                [
                    'description' => 'Seeded milestone for the Project Accomplishments MVP.',
                    'target_date' => Carbon::today()->addDays($targetOffset),
                    'completion_date' => $completionOffset !== null
                        ? Carbon::today()->addDays($completionOffset)
                        : null,
                    'percent_complete' => $percent,
                    'status' => $status,
                    'reported_by' => $admin?->id,
                    'validated_by' => $status === 'Completed' ? $admin?->id : null,
                    'validated_at' => $status === 'Completed' ? now() : null,
                    'remarks' => $status === 'Delayed'
                        ? 'Requires follow-up from the monitoring team.'
                        : null,
                    'is_archived' => false,
                ]
            );
        }

        // Keep each project dashboard percentage aligned with its milestone records.
        foreach ($projects as $project) {
            $average = $project->accomplishments()
                ->where('is_archived', false)
                ->avg('percent_complete');

            $project->update(['progress_percent' => round((float) ($average ?? 0), 2)]);
        }
    }
}
