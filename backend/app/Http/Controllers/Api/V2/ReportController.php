<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    private const MODULE = 'reports';

    public function projectStatus(Request $request): JsonResponse
    {
        $user = $this->authorizeModule($request, 'view');

        $baseQuery = Project::query()
            ->where('is_archived', false)
            ->when($request->query('date_from'), fn ($q, $date) =>
                $q->where('target_start_date', '>=', $date)
            )
            ->when($request->query('date_to'), fn ($q, $date) =>
                $q->where('target_end_date', '<=', $date)
            )
            ->when($request->query('status'), fn ($q, $status) =>
                $q->where('status', $status)
            );

        $totalProjects = (clone $baseQuery)->count();
        $totalBudget = (clone $baseQuery)->sum('approved_budget');
        $avgCompletion = (clone $baseQuery)->avg('progress_percent');

        $projects = (clone $baseQuery)
            ->with('contracts.contractor')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($project) {
                $contractorName = $project->contracts->first()?->contractor?->company_name;

                return [
                    'name' => $project->project_name,
                    'contractor' => $contractorName ?? $project->implementing_office ?? '-',
                    'status' => $project->status,
                    'completion' => (int) $project->progress_percent . '%',
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'report_type' => 'project-status',
                'generated_at' => now()->toIso8601String(),
                'stats' => [
                    'total_projects' => $totalProjects,
                    'total_budget' => (float) $totalBudget,
                    'avg_completion' => round((float) $avgCompletion, 1),
                ],
                'projects' => $projects,
            ],
            'meta' => [
                'permissions' => [
                    'can_export' => $user->canModule(self::MODULE, 'export'),
                ],
            ],
        ]);
    }

    private function authorizeModule(Request $request, string $ability): User
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Authentication required.');
        abort_unless(
            $user->canModule(self::MODULE, $ability),
            Response::HTTP_FORBIDDEN,
            'You do not have permission to view this report.'
        );

        return $user;
    }
}
