<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngineeringPlanRequest;
use App\Models\EngineeringPlan;
use App\Services\EngineeringPlanFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EngineeringPlanController extends Controller
{
    public function __construct(
        private readonly EngineeringPlanFileService $fileService
    ) {}

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
                'data' => $plan->fresh(['project', 'uploader']),
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
}
