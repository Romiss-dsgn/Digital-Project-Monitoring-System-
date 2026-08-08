<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
            ->with(['contractor', 'contracts.contractor'])
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
            ->with(['contractor', 'contracts.contractor'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($project) => $this->formatProject($project))
            ->values()
            ->all();

        $efficiencyBase = max($totalProjects, 1);
        $municipalEfficiency = round((($onTimeProjects + $completedProjects) / $efficiencyBase) * 100, 1);

        return response()->json([
            'data' => $projects->items(),
            'stats' => [
                'total_projects' => $totalProjects,
                'delayed_works' => $delayedWorks,
                'municipal_efficiency' => $municipalEfficiency,
                'total_budget' => $totalBudget,
                'last_updated_at' => $lastUpdatedAt,
                'location_distribution' => $distribution,
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
        $contractorName = $project->contractor?->company_name ?? $project->contracts->first()?->contractor?->company_name;

        return [
            'id' => $project->id,
            'code' => $project->project_code,
            'name' => $project->project_name,
            'location' => $project->location,
            'contractor_id' => $project->contractor_id,
            'contractor' => $contractorName ?? $project->implementing_office ?? '-',
            'budget' => $project->approved_budget,
            'progress' => (int) $project->progress_percent,
            'phase' => $project->phase,
            'status' => $this->effectiveProjectStatus($project),
            'start_date' => $project->target_start_date?->format('Y-m-d'),
            'end_date' => $project->target_end_date?->format('Y-m-d'),
            'duration_days' => $this->durationDays($project->target_start_date, $project->target_end_date),
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

    public function options(Request $request): JsonResponse
    {
        $this->authorizeModule($request, 'view');

        return response()->json([
            'contractors' => Contractor::query()
                ->where('is_active', true)
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
        ]);
    }

    private function resolveContractor(array $data, ?Project $project = null): ?Contractor
    {
        if (!empty($data['contractor_id'])) {
            return Contractor::query()
                ->where('is_active', true)
                ->find((int) $data['contractor_id']);
        }

        if (!empty($data['new_contractor_name'])) {
            $companyName = trim($data['new_contractor_name']);

            // MVP path: Infrastructure Plans can register a contractor by company name
            // without requiring the full contractor profile form yet.
            $contractor = Contractor::firstOrCreate(
                ['company_name' => $companyName],
                ['is_active' => true]
            );

            if (!$contractor->is_active) {
                $contractor->forceFill(['is_active' => true])->save();
            }

            return $contractor;
        }

        return $project?->contractor;
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->authorizeModule($request, 'create');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:projects,project_code'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'contractor_id' => [
                'nullable',
                'integer',
                Rule::exists('contractors', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'new_contractor_name' => ['nullable', 'string', 'max:255', 'required_without:contractor_id'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'phase' => ['required', 'string', Rule::in(['Planning', 'Construction', 'Post Evaluation'])],
            'status' => ['nullable', 'string', 'max:255'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'code.unique' => 'A project with this code already exists. Please use a different project code.',
            'new_contractor_name.required_without' => 'Please select a contractor or enter a new contractor name.',
            'contractor_id.exists' => 'The selected contractor is not active or does not exist.',
        ]);

        $project = DB::transaction(function () use ($data, $user) {
            $contractor = $this->resolveContractor($data);
            $status = $this->defaultProjectStatus($data['phase'], $data['startDate'] ?? null, $data['endDate'] ?? null, 0);

            $project = Project::create([
                'project_code' => $data['code'],
                'project_name' => $data['name'],
                'location' => $data['location'],
                'contractor_id' => $contractor?->id,
                'implementing_office' => $contractor?->company_name,
                'target_start_date' => $data['startDate'] ?? null,
                'target_end_date' => $data['endDate'] ?? null,
                'approved_budget' => $data['budget'] ?? 0,
                'phase' => $data['phase'],
                'status' => $status,
                'progress_percent' => 0,
                'description' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->logAction($user, 'created', self::MODULE, $project->id, $project->project_code, null, [
                'code' => $data['code'],
                'name' => $data['name'],
                'location' => $data['location'],
                'contractor' => $contractor?->company_name,
            ]);

            return $project->fresh(['contractor', 'contracts.contractor']);
        });

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
            'contractor_id' => [
                'nullable',
                'integer',
                Rule::exists('contractors', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'new_contractor_name' => ['nullable', 'string', 'max:255'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'phase' => ['sometimes', 'required', 'string', Rule::in(['Planning', 'Construction', 'Post Evaluation'])],
            'status' => ['nullable', 'string', 'max:255'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'code.unique' => 'A project with this code already exists. Please use a different project code.',
            'contractor_id.exists' => 'The selected contractor is not active or does not exist.',
        ]);

        $oldValues = [
            'project_code' => $project->project_code,
            'project_name' => $project->project_name,
            'location' => $project->location,
            'contractor_id' => $project->contractor_id,
        ];

        $project = DB::transaction(function () use ($data, $project, $oldValues, $user) {
            $contractor = $this->resolveContractor($data, $project);
            $shouldUpdateContractor = array_key_exists('contractor_id', $data) || !empty($data['new_contractor_name']);
            $phase = $data['phase'] ?? $project->phase;
            $startDate = $data['startDate'] ?? $project->target_start_date;
            $endDate = $data['endDate'] ?? $project->target_end_date;
            $derivedState = $this->derivedProjectState($project, $phase, $startDate, $endDate);

            $project->update([
                'project_code' => $data['code'] ?? $project->project_code,
                'project_name' => $data['name'] ?? $project->project_name,
                'location' => $data['location'] ?? $project->location,
                'contractor_id' => $shouldUpdateContractor ? $contractor?->id : $project->contractor_id,
                'implementing_office' => $shouldUpdateContractor ? $contractor?->company_name : $project->implementing_office,
                'target_start_date' => $startDate,
                'target_end_date' => $endDate,
                'approved_budget' => $data['budget'] ?? $project->approved_budget,
                'phase' => $phase,
                'status' => $derivedState['status'],
                'progress_percent' => $derivedState['progress'],
                'description' => $data['notes'] ?? $project->description,
            ]);

            $this->logAction($user, 'updated', self::MODULE, $project->id, $project->project_code, $oldValues, [
                'project_code' => $data['code'] ?? $project->project_code,
                'project_name' => $data['name'] ?? $project->project_name,
                'location' => $data['location'] ?? $project->location,
                'contractor_id' => $shouldUpdateContractor ? $contractor?->id : $project->contractor_id,
                'contractor' => $shouldUpdateContractor ? $contractor?->company_name : $project->contractor?->company_name,
            ]);

            return $project->fresh(['contractor', 'contracts.contractor']);
        });

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => $this->formatProject($project),
        ]);
    }

    private function derivedProjectState(Project $project, ?string $phase, mixed $startDate, mixed $endDate): array
    {
        $latest = ProjectAccomplishment::query()
            ->where('project_id', $project->id)
            ->where('is_archived', false)
            ->orderByDesc('target_date')
            ->orderByDesc('created_at')
            ->first();

        if ($latest) {
            return [
                'progress' => round((float) $latest->percent_complete, 2),
                'status' => $this->projectStatusFromAccomplishment($latest->status),
            ];
        }

        return [
            'progress' => 0,
            'status' => $this->defaultProjectStatus($phase, $startDate, $endDate, 0),
        ];
    }

    private function effectiveProjectStatus(Project $project): string
    {
        if ($project->status === 'completed') {
            return 'completed';
        }

        return $this->defaultProjectStatus(
            $project->phase,
            $project->target_start_date,
            $project->target_end_date,
            (float) $project->progress_percent,
            $project->status
        );
    }

    private function projectStatusFromAccomplishment(?string $status): string
    {
        return match ($status) {
            'Completed' => 'completed',
            'Delayed' => 'delayed',
            'Not Started' => 'planning',
            default => 'on_time',
        };
    }

    private function defaultProjectStatus(
        ?string $phase,
        mixed $startDate,
        mixed $endDate,
        float $progress,
        ?string $fallback = null
    ): string {
        $normalizedPhase = strtolower(trim((string) $phase));

        if ($progress >= 100) {
            return 'completed';
        }

        if ($normalizedPhase === 'planning') {
            return 'planning';
        }

        if ($endDate && Carbon::parse($endDate)->endOfDay()->isPast()) {
            return 'delayed';
        }

        return $fallback ?: 'on_time';
    }

    private function durationDays(mixed $startDate, mixed $endDate): ?int
    {
        if (! $startDate || ! $endDate) {
            return null;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lessThan($start)) {
            return null;
        }

        return $start->diffInDays($end) + 1;
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
