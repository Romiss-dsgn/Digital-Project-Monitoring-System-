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

    DB::beginTransaction();

    try {
        // FILE: only store if one was actually attached
        $fileInfo = null;
        if ($request->hasFile('file')) {
            $fileInfo = $this->fileService->store($request->file('file'));
        }

        $plan = EngineeringPlan::create([
            'project_id'  => $validated['project_id'] ?? null,
            'plan_title'  => $validated['plan_title']  ?? 'Test Plan',
            'plan_type'   => $validated['plan_type']   ?? null,
            'version'     => $validated['version']     ?? null,
            'status'      => $validated['status']      ?? EngineeringPlan::STATUS_FOR_REVIEW,
            'remarks'     => $validated['remarks']     ?? null,
            'file_name'   => $fileInfo['name'] ?? null,
            'file_path'   => $fileInfo['path'] ?? null,
            'file_type'   => $fileInfo['type'] ?? null,
            'uploaded_by' => $request->user()?->id,
            'uploaded_at' => now(),
        ]);

        DB::commit();

        return response()->json(['data' => $plan->fresh()], 201);

    } catch (\Throwable $e) {
        DB::rollBack();

        if (isset($fileInfo)) {
            $this->fileService->delete($fileInfo['path']);
        }

        Log::error('EngineeringPlan store failed', [
            'user_id' => $request->user()?->id,
            'error'   => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Failed to save engineering plan. Please try again.',
        ], 500);
    }
}
}