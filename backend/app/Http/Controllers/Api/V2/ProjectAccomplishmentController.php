<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\AccomplishmentDocument;
use App\Models\Project;
use App\Models\ProjectAccomplishment;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectAccomplishmentController extends Controller
{
    private const STATUSES = ['Not Started', 'In Progress', 'Delayed', 'Completed'];
    private const FORMULA_VERSION = 'linear-calendar-days-v1';
    private const DELAY_TOLERANCE_PERCENT = 2.0;

    public function index(Request $request): JsonResponse
    {
        $query = ProjectAccomplishment::query()
            ->with([
                'project:id,project_code,project_name,location,progress_percent,target_start_date,target_end_date',
                'documents.uploader:id,name',
                'reporter:id,name',
                'validator:id,name',
            ])
            ->where('is_archived', false)
            ->when($request->string('search')->toString(), function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('milestone_title', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $project) => $project
                            ->where('project_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->query('statuses'), function (Builder $query, array $statuses) {
                $query->whereIn('status', array_values(array_intersect($statuses, self::STATUSES)));
            })
            ->when($request->integer('project_id'), fn (Builder $query, int $projectId) => $query->where('project_id', $projectId))
            ->when($request->query('target_from'), fn (Builder $query, string $date) => $query->whereDate('target_date', '>=', $date))
            ->when($request->query('target_to'), fn (Builder $query, string $date) => $query->whereDate('target_date', '<=', $date))
            ->latest();

        $accomplishments = $query->paginate($this->perPage($request));

        return response()->json([
            'data' => collect($accomplishments->items())
                ->map(fn (ProjectAccomplishment $accomplishment) => $this->formatAccomplishment($accomplishment)),
            'meta' => $this->paginationMeta($accomplishments),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = ProjectAccomplishment::query()->where('is_archived', false);
        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'Completed')->count();
        $active = (clone $query)->where('status', 'In Progress')->count();

        $featured = (clone $query)
            ->with(['project.contracts' => fn ($contract) => $contract
                ->where('is_archived', false)
                ->select('id', 'project_id', 'contract_number')])
            ->where('status', '!=', 'Completed')
            ->orderByDesc('percent_complete')
            ->first();

        $recentActivity = (clone $query)
            ->with('project:id,project_code,project_name')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (ProjectAccomplishment $item) => [
                'id' => $item->id,
                'title' => $item->milestone_title,
                'project_name' => $item->project?->project_name,
                'status' => $item->status,
                'percent_complete' => (float) $item->percent_complete,
                'expected_percent' => (float) $item->expected_percent,
                'variance_percent' => (float) $item->variance_percent,
                'updated_at' => optional($item->updated_at)->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'data' => [
                'overall_progress' => $total > 0 ? round((float) (clone $query)->avg('percent_complete'), 1) : 0,
                'milestones_completed' => $completed,
                'milestones_total' => $total,
                'active_milestones' => $active,
                'delayed_tasks' => (clone $query)->where('status', 'Delayed')->count(),
                'featured' => $featured ? [
                    'project_name' => $featured->project?->project_name,
                    'contract_number' => $featured->project?->contracts?->first()?->contract_number,
                    'milestone_title' => $featured->milestone_title,
                    'percent_complete' => (float) $featured->percent_complete,
                    'expected_percent' => (float) $featured->expected_percent,
                    'variance_percent' => (float) $featured->variance_percent,
                    'target_date' => optional($featured->target_date)->format('Y-m-d'),
                ] : null,
                'recent_activity' => $recentActivity,
                'permissions' => $request->user()->modulePermissions('project_accomplishments'),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        return response()->json([
            'projects' => Project::query()
                ->where('is_archived', false)
                ->orderBy('project_name')
                ->get(['id', 'project_code', 'project_name', 'location', 'target_start_date', 'target_end_date', 'progress_percent']),
            'all_projects_count' => Project::query()->where('is_archived', false)->count(),
            'statuses' => self::STATUSES,
            'formula' => [
                'version' => self::FORMULA_VERSION,
                'delay_tolerance_percent' => self::DELAY_TOLERANCE_PERCENT,
                'description' => 'Expected % = elapsed calendar days / total project calendar days * 100.',
            ],
            'permissions' => $request->user()->modulePermissions('project_accomplishments'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedAccomplishment($request);
        $attachment = $request->file('attachment');
        $storedPath = null;

        try {
            $accomplishment = DB::transaction(function () use ($request, $validated, $attachment, &$storedPath) {
                $attributes = collect($validated)->except('attachment')->all();
                $attributes = $this->applyScheduleFormula($attributes);
                $attributes['reported_by'] = $request->user()->id;

                // ── NULL fix: never persist remarks/description as NULL ──
                $attributes['remarks'] = $attributes['remarks'] ?? '';
                $attributes['description'] = $attributes['description'] ?? '';

                // ── Auto-validate on create ──
                $attributes['completion_date'] = $attributes['status'] === 'Completed'
                    ? ($attributes['completion_date'] ?? now()->toDateString())
                    : ($attributes['completion_date'] ?? null);

                $accomplishment = ProjectAccomplishment::create($attributes + ['is_archived' => false]);

                if ($attachment) {
                    $storedPath = $attachment->store("accomplishment-documents/{$accomplishment->id}", 'local');
                    $this->createDocument($request, $accomplishment, $attachment, $storedPath);
                }

                $this->syncProjectProgress($accomplishment->project_id);
                AuditLogger::record(
                    $request,
                    'created',
                    'project_accomplishments',
                    $accomplishment->id,
                    $accomplishment->project?->project_code,
                    null,
                    $accomplishment->toArray()
                );

                return $accomplishment;
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Project accomplishment created successfully.',
            'data' => $this->formatAccomplishment($accomplishment->load(['project', 'documents', 'reporter', 'validator'])),
        ], 201);
    }

    public function show(ProjectAccomplishment $accomplishment): JsonResponse
    {
        abort_if($accomplishment->is_archived, 404);

        return response()->json([
            'data' => $this->formatAccomplishment(
                $accomplishment->load(['project', 'documents.uploader:id,name', 'reporter:id,name', 'validator:id,name'])
            ),
        ]);
    }

    public function update(Request $request, ProjectAccomplishment $accomplishment): JsonResponse
    {
        abort_if($accomplishment->is_archived, 404);
        $validated = collect($this->validatedAccomplishment($request, $accomplishment->id))->except('attachment')->all();
        $validated = $this->applyScheduleFormula($validated);

        $validated['remarks'] = $validated['remarks'] ?? '';
        $validated['description'] = $validated['description'] ?? '';

        $oldValues = $accomplishment->toArray();
        $oldProjectId = $accomplishment->project_id;

        DB::transaction(function () use ($request, $accomplishment, $validated, $oldValues, $oldProjectId) {
            $validated['completion_date'] = $validated['status'] === 'Completed'
                ? ($validated['completion_date'] ?? now()->toDateString())
                : ($validated['completion_date'] ?? null);

            $accomplishment->update($validated);
            $this->syncProjectProgress($accomplishment->project_id);
            if ($oldProjectId !== $accomplishment->project_id) {
                $this->syncProjectProgress($oldProjectId);
            }

            AuditLogger::record(
                $request,
                'updated',
                'project_accomplishments',
                $accomplishment->id,
                $accomplishment->project?->project_code,
                $oldValues,
                $accomplishment->fresh()->toArray()
            );
        });

        return response()->json([
            'message' => 'Project accomplishment updated successfully.',
            'data' => $this->formatAccomplishment($accomplishment->fresh(['project', 'documents', 'reporter', 'validator'])),
        ]);
    }

    public function validateRecord(Request $request, ProjectAccomplishment $accomplishment): JsonResponse
    {
        abort_if($accomplishment->is_archived, 404);
        $oldValues = $accomplishment->toArray();

        $accomplishment->update([
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);

        AuditLogger::record(
            $request,
            'validated',
            'project_accomplishments',
            $accomplishment->id,
            $accomplishment->project?->project_code,
            $oldValues,
            $accomplishment->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Accomplishment validated successfully.',
            'data' => $this->formatAccomplishment($accomplishment->fresh(['project', 'documents', 'reporter', 'validator'])),
        ]);
    }

    public function destroy(Request $request, ProjectAccomplishment $accomplishment): JsonResponse
    {
        abort_if($accomplishment->is_archived, 404);
        $oldValues = $accomplishment->toArray();
        $projectId = $accomplishment->project_id;

        DB::transaction(function () use ($request, $accomplishment, $oldValues, $projectId) {
            $accomplishment->update(['is_archived' => true]);
            $this->syncProjectProgress($projectId);
            AuditLogger::record(
                $request,
                'archived',
                'project_accomplishments',
                $accomplishment->id,
                $accomplishment->project?->project_code,
                $oldValues,
                $accomplishment->fresh()->toArray()
            );
        });

        return response()->json(['message' => 'Accomplishment archived successfully.']);
    }

    public function uploadDocument(Request $request, ProjectAccomplishment $accomplishment): JsonResponse
    {
        abort_if($accomplishment->is_archived, 404);
        $validated = $request->validate([
            'attachment' => ['required', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:25600'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        $file = $request->file('attachment');
        $path = $file->store("accomplishment-documents/{$accomplishment->id}", 'local');

        try {
            $document = DB::transaction(function () use ($request, $accomplishment, $file, $path, $validated) {
                return $this->createDocument(
                    $request,
                    $accomplishment,
                    $file,
                    $path,
                    $validated['remarks'] ?? ''
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return response()->json([
            'message' => 'Accomplishment document uploaded successfully.',
            'data' => $this->formatDocument($document->load('uploader:id,name')),
        ], 201);
    }

    public function downloadDocument(AccomplishmentDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Stored file was not found.');

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    private function validatedAccomplishment(Request $request, ?int $ignoreAccomplishmentId = null): array
    {
        return $request->validate([
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'milestone_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_accomplishments', 'milestone_title')
                    ->where(fn ($query) => $query
                        ->where('project_id', $request->integer('project_id'))
                        ->where('is_archived', false))
                    ->ignore($ignoreAccomplishmentId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'target_date' => ['required', 'date'],
            'report_period' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date'],
            'percent_complete' => ['required', 'numeric', 'between:0,100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:25600'],
        ], [
            'milestone_title.unique' => 'This project already has an active milestone with the same title.',
        ]);
    }

    private function applyScheduleFormula(array $attributes): array
    {
        $project = Project::find($attributes['project_id']);
        $targetDate = ! empty($attributes['target_date'])
            ? Carbon::parse($attributes['target_date'])->startOfDay()
            : null;
        $actualPercent = round((float) ($attributes['percent_complete'] ?? 0), 2);

        $durationDays = null;
        $elapsedDays = null;
        $expectedPercent = 0.0;

        if ($project?->target_start_date && $project?->target_end_date && $targetDate) {
            $startDate = Carbon::parse($project->target_start_date)->startOfDay();
            $endDate = Carbon::parse($project->target_end_date)->startOfDay();

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
        }

        $attributes['report_period'] = $attributes['report_period']
            ?? ($targetDate ? $targetDate->copy()->startOfMonth()->toDateString() : null);
        $attributes['expected_percent'] = $expectedPercent;
        $attributes['variance_percent'] = round($actualPercent - $expectedPercent, 2);
        $attributes['elapsed_days'] = $elapsedDays;
        $attributes['duration_days'] = $durationDays;
        $attributes['formula_version'] = self::FORMULA_VERSION;
        $attributes['status'] = $this->computedStatus($actualPercent, $expectedPercent);

        return $attributes;
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

    private function createDocument(
        Request $request,
        ProjectAccomplishment $accomplishment,
        $file,
        string $path,
        string $remarks = ''
    ): AccomplishmentDocument {
        $document = AccomplishmentDocument::create([
            'project_accomplishment_id' => $accomplishment->id,
            'document_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now(),
            'remarks' => $remarks,
        ]);

        AuditLogger::record(
            $request,
            'uploaded',
            'accomplishment_documents',
            $document->id,
            $accomplishment->project?->project_code,
            null,
            $document->toArray()
        );

        return $document;
    }

    /**
     * Project progress follows the latest monthly accomplishment record.
     */
    private function syncProjectProgress(int $projectId): void
    {
        $project = Project::find($projectId);

        if (! $project) {
            return;
        }

        $latest = ProjectAccomplishment::query()
            ->where('project_id', $projectId)
            ->where('is_archived', false)
            ->orderByDesc('target_date')
            ->orderByDesc('created_at')
            ->first();

        if (! $latest) {
            $project->update([
                'progress_percent' => 0,
                'status' => strtolower((string) $project->phase) === 'planning' ? 'planning' : 'ongoing',
            ]);

            return;
        }

        $project->update([
            'progress_percent' => round((float) $latest->percent_complete, 2),
            'status' => $this->projectStatusFromAccomplishment($latest),
        ]);
    }

    private function projectStatusFromAccomplishment(ProjectAccomplishment $accomplishment): string
    {
        return match ($accomplishment->status) {
            'Completed' => 'completed',
            'Delayed' => 'delayed',
            'Not Started' => 'planning',
            default => 'on_time',
        };
    }

    private function formatAccomplishment(ProjectAccomplishment $accomplishment): array
    {
        $expectedPercent = $this->expectedPercent($accomplishment);

        return [
            'id' => $accomplishment->id,
            'project_id' => $accomplishment->project_id,
            'project_code' => $accomplishment->project?->project_code,
            'project_name' => $accomplishment->project?->project_name,
            'project_location' => $accomplishment->project?->location,
            'milestone_title' => $accomplishment->milestone_title,
            'description' => $accomplishment->description,
            'target_date' => optional($accomplishment->target_date)->format('Y-m-d'),
            'report_period' => optional($accomplishment->report_period)->format('Y-m-d'),
            'completion_date' => optional($accomplishment->completion_date)->format('Y-m-d'),
            'percent_complete' => (float) $accomplishment->percent_complete,
            'expected_percent' => $expectedPercent,
            'variance_percent' => round((float) $accomplishment->percent_complete - $expectedPercent, 2),
            'elapsed_days' => $accomplishment->elapsed_days,
            'duration_days' => $accomplishment->duration_days,
            'performance_alignment' => $this->performanceAlignment($accomplishment),
            'formula_version' => $accomplishment->formula_version,
            'status' => $accomplishment->status,
            'reported_by' => $accomplishment->reporter?->name,
            'validated_by' => $accomplishment->validator?->name,
            'validated_at' => optional($accomplishment->validated_at)->format('Y-m-d H:i:s'),
            'remarks' => $accomplishment->remarks,
            'documents' => $accomplishment->relationLoaded('documents')
                ? $accomplishment->documents->map(fn (AccomplishmentDocument $document) => $this->formatDocument($document))
                : [],
            'created_at' => optional($accomplishment->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($accomplishment->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function formatDocument(AccomplishmentDocument $document): array
    {
        return [
            'id' => $document->id,
            'document_title' => $document->document_title,
            'file_name' => $document->file_name,
            'file_type' => $document->file_type,
            'uploaded_by' => $document->uploader?->name,
            'uploaded_at' => optional($document->uploaded_at)->format('Y-m-d H:i:s'),
            'remarks' => $document->remarks,
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 10), 1), 100);
    }

    private function performanceAlignment(ProjectAccomplishment $accomplishment): float
    {
        $project = $accomplishment->project;
        $targetDate = $accomplishment->target_date;

        if (! $project?->target_start_date || ! $project?->target_end_date || ! $targetDate) {
            return 0.0;
        }

        $startDate = Carbon::parse($project->target_start_date)->startOfDay();
        $endDate = Carbon::parse($project->target_end_date)->startOfDay();
        $durationDays = max(1, $startDate->diffInDays($endDate) + 1);
        $elapsedDays = $startDate->diffInDays(Carbon::parse((string) $targetDate)->startOfDay()) + 1;

        return round(50 * (sin(deg2rad((180 * ($elapsedDays / $durationDays)) - 90)) + 1), 2);
    }

    private function expectedPercent(ProjectAccomplishment $accomplishment): float
    {
        $project = $accomplishment->project;
        $targetDate = $accomplishment->target_date;

        if (! $project?->target_start_date || ! $project?->target_end_date || ! $targetDate) {
            return (float) $accomplishment->expected_percent;
        }

        $startDate = Carbon::parse($project->target_start_date)->startOfDay();
        $endDate = Carbon::parse($project->target_end_date)->startOfDay();
        $targetDate = Carbon::parse((string) $targetDate)->startOfDay();

        if ($endDate->lessThan($startDate)) {
            $endDate = $startDate->copy();
        }

        $durationDays = max(1, $startDate->diffInDays($endDate) + 1);
        $elapsedDays = $targetDate->lessThan($startDate)
            ? 0
            : ($targetDate->greaterThan($endDate)
                ? $durationDays
                : min($durationDays, $startDate->diffInDays($targetDate) + 1));

        return round(min(100, max(0, ($elapsedDays / $durationDays) * 100)), 2);
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
