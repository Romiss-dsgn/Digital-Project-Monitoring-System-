<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RegisterController extends Controller
{
        /**
     * Handle the incoming request.
     *
     * @param \App\Http\Requests\Api\V2\Auth\RegisterRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'username' => $request->email,
            'name' => $request->name,
            'email' => $request->email,
            'badge_number' => $request->badge_number,
            'department' => $request->department,
            'requested_role' => $request->requested_role,
            'is_active' => false,
            'access_status' => 'pending',
            'access_requested_at' => now(),
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Access request submitted successfully. Please wait for administrator approval before logging in.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'access_status' => $user->access_status,
            ],
        ], Response::HTTP_CREATED);
    }
}
