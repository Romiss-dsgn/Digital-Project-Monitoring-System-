<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngineeringPlanRequest;
use App\Models\EngineeringPlan;
use App\Services\AuditLogger;
use App\Services\EngineeringPlanFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'reviewer:id,name,email',
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

            AuditLogger::record(
                $request,
                'created',
                'engineering_plans',
                $plan->id,
                $plan->file_name,
                null,
                $plan->fresh()->toArray()
            );

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

    public function show(EngineeringPlan $engineeringPlan): JsonResponse
    {
        abort_if($engineeringPlan->is_archived, 404);

        return response()->json([
            'data' => $this->formatPlan($engineeringPlan->load(['project', 'uploader', 'reviewer'])),
        ]);
    }

    public function updateStatus(Request $request, EngineeringPlan $engineeringPlan): JsonResponse
    {
        abort_if($engineeringPlan->is_archived, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(EngineeringPlan::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldValues = $engineeringPlan->toArray();

        // Review actions do not replace the uploaded file; they only update the review trail.
        $engineeringPlan->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? $engineeringPlan->remarks,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        AuditLogger::record(
            $request,
            'reviewed',
            'engineering_plans',
            $engineeringPlan->id,
            $engineeringPlan->file_name,
            $oldValues,
            $engineeringPlan->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Engineering plan status updated.',
            'data' => $this->formatPlan($engineeringPlan->fresh(['project', 'uploader', 'reviewer'])),
        ]);
    }

    public function destroy(Request $request, EngineeringPlan $engineeringPlan): JsonResponse
    {
        abort_if($engineeringPlan->is_archived, 404);

        $oldValues = $engineeringPlan->toArray();
        $engineeringPlan->update(['is_archived' => true]);

        AuditLogger::record(
            $request,
            'archived',
            'engineering_plans',
            $engineeringPlan->id,
            $engineeringPlan->file_name,
            $oldValues,
            $engineeringPlan->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Engineering plan archived successfully.',
        ]);
    }

    public function download(EngineeringPlan $engineeringPlan): StreamedResponse
    {
        abort_if($engineeringPlan->is_archived, 404);
        abort_unless(Storage::disk('public')->exists($engineeringPlan->file_path), 404, 'Stored file was not found.');

        return Storage::disk('public')->download($engineeringPlan->file_path, $engineeringPlan->file_name);
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
            'reviewed_by' => $plan->reviewer?->name ?? $plan->reviewer?->email,
            'reviewed_at' => $plan->reviewed_at?->toIso8601String(),
            'remarks' => $plan->remarks,
            'created_at' => $plan->created_at?->toIso8601String(),
            'updated_at' => $plan->updated_at?->toIso8601String(),
        ];
    }
}
