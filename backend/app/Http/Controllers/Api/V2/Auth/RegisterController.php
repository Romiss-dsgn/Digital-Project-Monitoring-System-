<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\RegisterRequest;
use App\Models\AccessRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $requestedRoleId = $this->resolveRequestedRoleId($request);

        if (! $requestedRoleId) {
            return response()->json([
                'message' => 'The selected role is not available.',
                'errors' => [
                    'requested_role' => ['The selected role is not available.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $accessRequest = AccessRequest::create([
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'badge_number' => $request->input('badge_number'),
            'contact_number' => $request->input('contact_number'),
            'position' => $request->input('position'),
            'office_unit' => $request->input('office_unit'),
            'requested_role_id' => $requestedRoleId,
            'reason' => $request->input('reason'),
            'status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Access request submitted successfully. Please wait for administrator approval before logging in.',
            'access_request' => [
                'id' => $accessRequest->id,
                'full_name' => $accessRequest->full_name,
                'email' => $accessRequest->email,
                'badge_number' => $accessRequest->badge_number,
                'status' => $accessRequest->status,
                'requested_role' => $accessRequest->requestedRole?->name,
            ],
        ], Response::HTTP_CREATED);
    }

    private function resolveRequestedRoleId(RegisterRequest $request): ?int
    {
        if ($request->filled('requested_role_id')) {
            return (int) $request->input('requested_role_id');
        }

        $roleSlug = (string) $request->input('requested_role');

        $roleName = match ($roleSlug) {
            'administrative-staff' => 'Administrative Staff - Contract Documentation',
            'records-management-personnel' => 'Records Management Personnel',
            'contract-monitoring-personnel' => 'Contract Monitoring Personnel',
            'engineer-planning' => 'Engineer - Planning',
            'engineer-supervision' => 'Engineer - Supervision',
            'engineer-monitoring' => 'Engineer - Monitoring',
            'system-administrator' => 'System Administrator',
            default => Str::of($roleSlug)->replace('-', ' ')->title()->toString(),
        };

        return Role::where('name', $roleName)->value('id');
    }
}
