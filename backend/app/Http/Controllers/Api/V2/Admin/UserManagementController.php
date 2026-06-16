<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $users = User::query()
            ->with('role')
            ->when($request->query('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($users);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json($user->load('role'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'badge_number' => ['nullable', 'string', 'max:255', 'unique:users,badge_number,' . $user->id],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'office_unit' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->load('role'),
        ]);
    }

    private function authorizeAdmin(Request $request): User
    {
        $admin = $request->user();

        abort_unless($admin?->role?->name === 'System Administrator', Response::HTTP_FORBIDDEN);

        return $admin;
    }
}
