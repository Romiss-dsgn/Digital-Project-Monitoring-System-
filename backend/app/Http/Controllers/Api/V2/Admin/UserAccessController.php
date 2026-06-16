<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserAccessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $status = $request->query('status', 'Pending');

        $accessRequests = AccessRequest::query()
            ->with(['requestedRole', 'reviewer'])
            ->when($status !== 'All', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($accessRequests);
    }

    public function approve(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        $admin = $this->authorizeAdmin($request);

        $data = $request->validate([
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $roleId = $data['role_id'] ?? $accessRequest->requested_role_id;

        if (! $roleId) {
            return response()->json([
                'message' => 'A role is required before approving this access request.',
                'errors' => [
                    'role_id' => ['A role is required before approving this access request.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $accessRequest->email)->first();
        $attributes = [
            'username' => $accessRequest->email,
            'name' => $accessRequest->full_name,
            'email' => $accessRequest->email,
            'badge_number' => $accessRequest->badge_number,
            'contact_number' => $accessRequest->contact_number,
            'position' => $accessRequest->position,
            'office_unit' => $accessRequest->office_unit,
            'role_id' => $roleId,
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        if ($user) {
            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);
        } else {
            $user = User::create($attributes + [
                'password' => $data['password'] ?? Str::random(32),
            ]);
        }

        $accessRequest->update([
            'status' => 'Approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Access request approved successfully.',
            'user' => $user->load('role'),
            'access_request' => $accessRequest->load(['requestedRole', 'reviewer']),
        ]);
    }

    public function reject(Request $request, AccessRequest $accessRequest): JsonResponse
    {
        $admin = $this->authorizeAdmin($request);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $accessRequest->update([
            'status' => 'Rejected',
            'reason' => $data['reason'] ?? $accessRequest->reason,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Access request rejected successfully.',
            'access_request' => $accessRequest->load(['requestedRole', 'reviewer']),
        ]);
    }

    private function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user?->role?->name === 'System Administrator', Response::HTTP_FORBIDDEN);

        return $user;
    }
}
