<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $requestedRoleId = $this->resolveRequestedRoleId($request);

        if (!$requestedRoleId) {
            return response()->json([
                'message' => 'Invalid role selected.',
                'errors' => [
                    'requested_role' => ['Invalid role selected.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::create([
            'name'            => $request->input('full_name'),
            'username'        => null,
            'email'           => $request->input('email'),
            'badge_number'    => $request->input('badge_number'),
            'contact_number'  => $request->input('contact_number'),
            'position'        => $request->input('position'),
            'office_unit'     => $request->input('office_unit'),
            'role_id'         => $requestedRoleId,
            'is_active'       => false,
            'password'        => $request->input('password'),
        ]);

        return response()->json([
            'message' => 'Access request submitted successfully. Please wait for admin approval.',
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'status'   => 'pending',
                'role'     => $user->role?->name,
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
            default => null,
        };

        if (!$roleName) {
            return null;
        }

        return Role::where('name', $roleName)->value('id');
    }
}
