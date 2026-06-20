<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    // GET ALL ROLES
    public function roles(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);

        return response()->json([
            'data' => $roles,
        ]);
    }

    // GET USERS
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $users = User::query()
            ->with('role')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('badge_number', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('office_unit', 'like', "%{$search}%");
                });
            })
            ->when($request->query('role'), fn($q, $role) =>
                $q->whereHas('role', fn($r) => $r->where('name', $role))
            )
            ->when($request->query('status') === 'active', fn($q) =>
                $q->where('is_active', true)
            )
            ->when($request->query('status') === 'inactive', fn($q) =>
                $q->where('is_active', false)->whereNotNull('rejected_at')
            )
            ->when($request->query('status') === 'pending', fn($q) =>
                $q->where('is_active', false)->whereNull('rejected_at')
            )
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 10));

        $users->getCollection()->transform(fn($u) => $this->formatUser($u));

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    // GET STATS
    public function stats(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $total = User::count();

        $lastMonth = User::where('created_at', '>=', now()->subMonth())->count();

        return response()->json([
            'total_users' => $total,
            'growth_pct' => $lastMonth,
            'active_now' => User::where('is_active', true)->count(),
            'pending_requests' => User::where('is_active', false)
                ->whereNull('accepted_at')
                ->whereNull('rejected_at')
                ->count(),
            'roles_defined' => Role::count(),
        ]);
    }

    // GET USER
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);
        return response()->json($this->formatUser($user->load('role')));
    }

    // CREATE USER
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required'],
            'username' => ['nullable', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'badge_number' => ['nullable'],
            'contact_number' => ['nullable'],
            'position' => ['nullable'],
            'office_unit' => ['nullable'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        // Password hashing handled automatically by User model mutator
        $data['is_active'] = false;

        $user = User::create($data);

        return response()->json([
            'message' => 'User created',
            'user' => $this->formatUser($user->load('role')),
        ]);
    }

    // UPDATE USER
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['sometimes'],
            'username' => ['sometimes', 'nullable', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['nullable', 'exists:roles,id'],
            'status' => ['sometimes', 'in:active,inactive,pending'],
        ]);

        if (isset($data['status'])) {
            $data['is_active'] = $data['status'] === 'active';
            unset($data['status']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Updated',
            'user' => $this->formatUser($user->fresh('role')),
        ]);
    }

    // UPDATE STATUS (active/inactive toggle)
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update(['is_active' => $data['is_active']]);

        return response()->json([
            'message' => 'Status updated',
            'user' => $this->formatUser($user->fresh('role')),
        ]);
    }

    // ACCEPT PENDING USER
    // Marks the request as accepted by admin. Account stays inactive
    // until the user successfully logs in for the first time
    // (activation happens in LoginController).
    public function accept(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $user->update([
            'accepted_at' => Carbon::now(),
            'rejected_at' => null,
        ]);

        return response()->json([
            'message' => 'User accepted. Account will activate on first login.',
            'user' => $this->formatUser($user->fresh('role')),
        ]);
    }

    // REJECT PENDING USER
    // Marks the request as rejected. Account is deactivated but
    // NOT deleted immediately — removal happens via a separate process.
    public function reject(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $user->update([
            'is_active' => false,
            'accepted_at' => null,
            'rejected_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'User rejected.',
            'user' => $this->formatUser($user->fresh('role')),
        ]);
    }

    // DELETE USER
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $user->delete();

        return response()->json([
            'message' => 'User deleted',
        ]);
    }

    // FORMAT USER
    private function formatUser(User $user): array
    {
        if ($user->is_active) {
            $status = 'active';
        } elseif ($user->rejected_at) {
            $status = 'inactive';
        } else {
            $status = 'pending';
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'badge_number' => $user->badge_number,
            'contact_number' => $user->contact_number,
            'position' => $user->position,
            'office_unit' => $user->office_unit,
            'role' => $user->role?->name,
            'role_id' => $user->role_id,
            'status' => $status,
            'accepted_at' => $user->accepted_at,
            'rejected_at' => $user->rejected_at,
            'last_active' => $user->last_active_at ? Carbon::parse($user->last_active_at)->diffForHumans() : 'Never',
            'avatar_url' => null,
        ];
    }

    private function authorizeAdmin(Request $request): User
    {
        $admin = $request->user();

        abort_unless(
            $admin?->role?->name === 'System Administrator',
            Response::HTTP_FORBIDDEN
        );

        return $admin;
    }
}
