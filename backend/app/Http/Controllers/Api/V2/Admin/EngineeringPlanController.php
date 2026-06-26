<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngineeringPlanRequest;
use App\Models\EngineeringPlan;
use App\Services\EngineeringPlanFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EngineeringPlanController extends Controller
{
    public function __construct(
        private readonly EngineeringPlanFileService $fileService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $baseQuery = EngineeringPlan::query()
            ->with([
                'project:id,project_code,project_name',
                'uploader:id,name,email',
            ])
            ->where('is_archived', false)
            ->when($request->query('type'), fn ($query, $type) => $query->where('plan_type', $type))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('plan_title', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhereHas('project', function ($projectQuery) use ($search) {
                            $projectQuery->where('project_name', 'like', "%{$search}%")
                                ->orWhere('project_code', 'like', "%{$search}%");
                        });
                });
            });

        $plans = (clone $baseQuery)
            ->orderByDesc('uploaded_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));

        $plans->getCollection()->transform(fn (EngineeringPlan $plan) => $this->formatPlan($plan));

        $statsQuery = EngineeringPlan::query()->where('is_archived', false);

        return response()->json([
            'data' => $plans->items(),
            'stats' => [
                'total_documents' => (clone $statsQuery)->count(),
                'pending_review' => (clone $statsQuery)->where('status', EngineeringPlan::STATUS_FOR_REVIEW)->count(),
                'approved_plans' => (clone $statsQuery)->where('status', EngineeringPlan::STATUS_APPROVED)->count(),
                'revisions_required' => (clone $statsQuery)->where('status', EngineeringPlan::STATUS_REVISION)->count(),
            ],
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
            ],
        ]);
    }

    public function store(StoreEngineeringPlanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $fileInfo = null;

        DB::beginTransaction();

        try {
            // Store only validated uploads; the request already requires project, title, type, status, and file.
            $fileInfo = $this->fileService->store($request->file('file'));

            $plan = EngineeringPlan::create([
                'project_id' => $validated['project_id'],
                'plan_title' => $validated['plan_title'],
                'plan_type' => $validated['plan_type'],
                'version' => $validated['version'] ?? null,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null,
                'file_name' => $fileInfo['name'],
                'file_path' => $fileInfo['path'],
                'file_type' => $fileInfo['type'],
                'uploaded_by' => $request->user()?->id,
                'uploaded_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'data' => $this->formatPlan($plan->fresh(['project', 'uploader'])),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($fileInfo) {
                $this->fileService->delete($fileInfo['path']);
            }

            Log::error('EngineeringPlan store failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to save engineering plan. Please try again.',
            ], 500);
        }
    }

    private function formatPlan(EngineeringPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'project_id' => $plan->project_id,
            'project_code' => $plan->project?->project_code,
            'project_name' => $plan->project?->project_name,
            'plan_title' => $plan->plan_title,
            'plan_type' => $plan->plan_type,
            'version' => $plan->version,
            'file_name' => $plan->file_name,
            'file_path' => $plan->file_path,
            'file_type' => $plan->file_type,
            'status' => $plan->status,
            'uploaded_by' => $plan->uploader?->name ?? $plan->uploader?->email,
            'uploaded_at' => $plan->uploaded_at?->toIso8601String(),
            'remarks' => $plan->remarks,
            'created_at' => $plan->created_at?->toIso8601String(),
            'updated_at' => $plan->updated_at?->toIso8601String(),
        ];
    }
}
