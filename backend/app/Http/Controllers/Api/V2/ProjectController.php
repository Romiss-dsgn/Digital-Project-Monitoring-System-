<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    private const MODULE = 'projects';

    public function index(Request $request): JsonResponse
    {
        $user = $this->authorizeModule($request, 'view');

        $baseQuery = Project::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->query('code'), fn ($q, $code) =>
                $q->where('project_code', 'like', "%{$code}%")
            )
            ->when($request->query('location'), fn($q, $location) =>
                $q->where('location', $location)
            )
            ->when($request->query('status'), fn($q, $status) =>
                $q->where('status', $status)
            )
            ->when($request->query('phase'), fn($q, $phase) =>
                $q->where('phase', $phase)
            )
            ->where('is_archived', false);

        $listQuery = (clone $baseQuery)
            ->with('contracts.contractor')
            ->orderBy('created_at', 'desc')
            ->orderByDesc('id');

        $projects = $listQuery->paginate($request->integer('per_page', 15));

        $projects->getCollection()->transform(fn($p) => $this->formatProject($p));

        $totalProjects = (clone $baseQuery)->count();
        $delayedWorks = (clone $baseQuery)->where('status', 'delayed')->count();
        $onTimeProjects = (clone $baseQuery)->where('status', 'on_time')->count();
        $completedProjects = (clone $baseQuery)->where('status', 'completed')->count();
        $totalBudget = (clone $baseQuery)->sum('approved_budget');
        $lastUpdatedAt = (clone $baseQuery)->max('updated_at');
        $distribution = (clone $baseQuery)
            ->selectRaw('location, COUNT(*) as total')
            ->groupBy('location')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'location' => $row->location,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $recentUpdates = (clone $baseQuery)
            ->with('contracts.contractor')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($project) => $this->formatProject($project))
            ->values()
            ->all();

        $efficiencyBase = max($totalProjects, 1);
        $regionalEfficiency = round((($onTimeProjects + $completedProjects) / $efficiencyBase) * 100, 1);

        return response()->json([
            'data' => $projects->items(),
            'stats' => [
                'total_projects' => $totalProjects,
                'delayed_works' => $delayedWorks,
                'regional_efficiency' => $regionalEfficiency,
                'total_budget' => $totalBudget,
                'last_updated_at' => $lastUpdatedAt,
                'regional_distribution' => $distribution,
                'recent_updates' => $recentUpdates,
            ],
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'permissions' => $this->projectPermissionsFor($user),
            ],
        ]);
    }

    private function formatProject(Project $project): array
    {
        $contractorName = $project->contracts->first()?->contractor?->company_name;

        return [
            'id' => $project->id,
            'code' => $project->project_code,
            'name' => $project->project_name,
            'location' => $project->location,
            'contractor' => $contractorName ?? $project->implementing_office ?? '-',
            'budget' => $project->approved_budget,
            'progress' => (int) $project->progress_percent,
            'phase' => $project->phase,
            'status' => $project->status,
            'start_date' => $project->target_start_date?->format('Y-m-d'),
            'end_date' => $project->target_end_date?->format('Y-m-d'),
            'notes' => $project->description,
            'created_at' => $project->created_at?->toIso8601String(),
            'updated_at' => $project->updated_at?->toIso8601String(),
        ];
    }

    private function authorizeModule(Request $request, string $ability): User
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Authentication required.');
        abort_unless(
            $user->canModule(self::MODULE, $ability),
            Response::HTTP_FORBIDDEN,
            'You do not have permission to perform this action on projects.'
        );

        return $user;
    }

    private function projectPermissionsFor(User $user): array
    {
        return [
            'can_create' => $user->canModule(self::MODULE, 'create'),
            'can_edit' => $user->canModule(self::MODULE, 'edit'),
            'can_delete' => $user->canModule(self::MODULE, 'delete'),
        ];
    }

    private function logAction(User $user, string $action, string $module, ?int $recordId, ?string $recordCode, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'record_code' => $recordCode,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->authorizeModule($request, 'create');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:projects,project_code'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'contractor' => ['nullable', 'string', 'max:255'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'phase' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:255'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'code.unique' => 'A project with this code already exists. Please use a different project code.',
        ]);

        $project = Project::create([
            'project_code' => $data['code'],
            'project_name' => $data['name'],
            'location' => $data['location'],
            'implementing_office' => $data['contractor'] ?? null,
            'target_start_date' => $data['startDate'] ?? null,
            'target_end_date' => $data['endDate'] ?? null,
            'approved_budget' => $data['budget'] ?? 0,
            'phase' => $data['phase'],
            'status' => $data['status'],
            'progress_percent' => $data['progress'] ?? 0,
            'description' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->logAction($user, 'created', self::MODULE, $project->id, $project->project_code, null, [
            'code' => $data['code'],
            'name' => $data['name'],
            'location' => $data['location'],
        ]);

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => $this->formatProject($project),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $user = $this->authorizeModule($request, 'edit');

        $data = $request->validate([
            'code' => ['sometimes', 'required', 'string', 'max:255', 'unique:projects,project_code,' . $project->id],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'contractor' => ['nullable', 'string', 'max:255'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'phase' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'max:255'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'code.unique' => 'A project with this code already exists. Please use a different project code.',
        ]);

        $oldValues = [
            'project_code' => $project->project_code,
            'project_name' => $project->project_name,
            'location' => $project->location,
        ];

        $project->update([
            'project_code' => $data['code'] ?? $project->project_code,
            'project_name' => $data['name'] ?? $project->project_name,
            'location' => $data['location'] ?? $project->location,
            'implementing_office' => $data['contractor'] ?? $project->implementing_office,
            'target_start_date' => $data['startDate'] ?? $project->target_start_date,
            'target_end_date' => $data['endDate'] ?? $project->target_end_date,
            'approved_budget' => $data['budget'] ?? $project->approved_budget,
            'phase' => $data['phase'] ?? $project->phase,
            'status' => $data['status'] ?? $project->status,
            'progress_percent' => $data['progress'] ?? $project->progress_percent,
            'description' => $data['notes'] ?? $project->description,
        ]);

        $this->logAction($user, 'updated', self::MODULE, $project->id, $project->project_code, $oldValues, [
            'project_code' => $data['code'] ?? $project->project_code,
            'project_name' => $data['name'] ?? $project->project_name,
            'location' => $data['location'] ?? $project->location,
        ]);

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => $this->formatProject($project->fresh()),
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $user = $this->authorizeModule($request, 'delete');

        $this->logAction($user, 'archived', self::MODULE, $project->id, $project->project_code, [
            'project_code' => $project->project_code,
            'project_name' => $project->project_name,
        ]);

        $project->update(['is_archived' => true]);

        return response()->json([
            'message' => 'Project archived successfully.',
        ]);
    }
}
