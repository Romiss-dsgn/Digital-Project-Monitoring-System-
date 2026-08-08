<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectAccomplishmentsSeeder extends Seeder
{
    private const FORMULA_VERSION = 'linear-calendar-days-v1';
    private const DELAY_TOLERANCE_PERCENT = 2.0;

    public function run(): void
    {
        $admin = User::where('email', 'admin@contrackpro.test')->first();

        $milestones = [
            [
                'project_code' => 'LGU-TUAO-PROJ-001',
                'milestone_title' => 'February 2026 Monthly SWA',
                'target_date' => '2026-02-15',
                'percent_complete' => 12,
                'remarks' => 'Initial civil works mobilization is aligned with the calendar schedule.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-002',
                'milestone_title' => 'March 2026 Monthly SWA',
                'target_date' => '2026-03-31',
                'percent_complete' => 25,
                'remarks' => 'Drainage work is behind the planned calendar output.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-003',
                'milestone_title' => 'April 2026 Monthly SWA',
                'target_date' => '2026-04-30',
                'percent_complete' => 28,
                'remarks' => 'Site development output is below the planned accomplishment curve.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-001',
                'milestone_title' => 'April 2026 Monthly SWA',
                'target_date' => '2026-04-30',
                'percent_complete' => 38,
                'remarks' => 'Structural and partition works are on schedule.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-002',
                'milestone_title' => 'May 2026 Monthly SWA',
                'target_date' => '2026-05-31',
                'percent_complete' => 57,
                'remarks' => 'Recovery work has caught up with the expected progress.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-003',
                'milestone_title' => 'June 2026 Monthly SWA',
                'target_date' => '2026-06-30',
                'percent_complete' => 45,
                'remarks' => 'Actual accomplishment remains behind planned schedule.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-001',
                'milestone_title' => 'September 2026 Monthly SWA',
                'target_date' => '2026-09-30',
                'percent_complete' => 86,
                'remarks' => 'Finishing works need acceleration before target completion.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-002',
                'milestone_title' => 'August 2026 Final SWA',
                'target_date' => '2026-08-31',
                'percent_complete' => 100,
                'completion_date' => '2026-08-28',
                'remarks' => 'Final accomplishment accepted for QA completion flow.',
            ],
            [
                'project_code' => 'LGU-TUAO-PROJ-003',
                'milestone_title' => 'August 2026 Monthly SWA',
                'target_date' => '2026-08-31',
                'percent_complete' => 65,
                'remarks' => 'Delayed sample used to verify dashboard and report alerts.',
            ],
        ];

        foreach ($milestones as $milestone) {
            $project = Project::where('project_code', $milestone['project_code'])->first();

            if (! $project) {
                continue;
            }

            $formula = $this->formulaFor($project, $milestone['target_date'], $milestone['percent_complete']);
            $status = $this->computedStatus(
                $milestone['percent_complete'],
                $formula['expected_percent']
            );

            ProjectAccomplishment::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'milestone_title' => $milestone['milestone_title'],
                ],
                [
                    'description' => 'QA seed accomplishment. Upload proof documents during E2E tests.',
                    'target_date' => $milestone['target_date'],
                    'report_period' => Carbon::parse($milestone['target_date'])->startOfMonth()->toDateString(),
                    'completion_date' => $milestone['completion_date'] ?? ($status === 'Completed' ? $milestone['target_date'] : null),
                    'percent_complete' => $milestone['percent_complete'],
                    'expected_percent' => $formula['expected_percent'],
                    'variance_percent' => $formula['variance_percent'],
                    'elapsed_days' => $formula['elapsed_days'],
                    'duration_days' => $formula['duration_days'],
                    'formula_version' => self::FORMULA_VERSION,
                    'status' => $status,
                    'reported_by' => $admin?->id,
                    'validated_by' => $status === 'Completed' ? $admin?->id : null,
                    'validated_at' => $status === 'Completed' ? now() : null,
                    'remarks' => $milestone['remarks'],
                    'is_archived' => false,
                ]
            );
        }

        Project::query()
            ->whereIn('project_code', collect($milestones)->pluck('project_code')->unique())
            ->get()
            ->each(function (Project $project) {
                $latest = ProjectAccomplishment::query()
                    ->where('project_id', $project->id)
                    ->where('is_archived', false)
                    ->orderByDesc('target_date')
                    ->orderByDesc('created_at')
                    ->first();

                if (! $latest) {
                    return;
                }

                $project->update([
                    'progress_percent' => $latest->percent_complete,
                    'status' => match ($latest->status) {
                        'Completed' => 'completed',
                        'Delayed' => 'delayed',
                        'Not Started' => 'planning',
                        default => 'on_time',
                    },
                ]);
            });
    }

    private function formulaFor(Project $project, string $targetDate, float $actualPercent): array
    {
        $startDate = Carbon::parse($project->target_start_date)->startOfDay();
        $endDate = Carbon::parse($project->target_end_date)->startOfDay();
        $targetDate = Carbon::parse($targetDate)->startOfDay();

        if ($endDate->lessThan($startDate)) {
            $endDate = $startDate->copy();
        }

        $durationDays = max(1, $startDate->diffInDays($endDate) + 1);

        if ($targetDate->lessThan($startDate)) {
            $elapsedDays = 0;
        } elseif ($targetDate->greaterThan($endDate)) {
            $elapsedDays = $durationDays;
        } else {
            $elapsedDays = min($durationDays, $startDate->diffInDays($targetDate) + 1);
        }

        $expectedPercent = round(min(100, max(0, ($elapsedDays / $durationDays) * 100)), 2);

        return [
            'expected_percent' => $expectedPercent,
            'variance_percent' => round($actualPercent - $expectedPercent, 2),
            'elapsed_days' => $elapsedDays,
            'duration_days' => $durationDays,
        ];
    }

    private function computedStatus(float $actualPercent, float $expectedPercent): string
    {
        if ($actualPercent >= 100) {
            return 'Completed';
        }

        if ($actualPercent <= 0 && $expectedPercent <= 0) {
            return 'Not Started';
        }

        if ($actualPercent + self::DELAY_TOLERANCE_PERCENT < $expectedPercent) {
            return 'Delayed';
        }

        return 'In Progress';
    }
}
