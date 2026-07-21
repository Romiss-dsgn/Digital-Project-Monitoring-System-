<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MeController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readProfile(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'errors' => [[
                    'status' => '401',
                    'title'  => 'Unauthenticated',
                    'detail' => 'No authenticated user found.',
                ]],
            ], 401);
        }

        return response()->json([
            'data' => [
                'type'       => 'users',
                'id'         => (string) $user->id,
                'attributes' => [
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    'badge_number'  => $user->badge_number,
                    'contact_number'=> $user->contact_number,
                    'position'      => $user->position,
                    'office_unit'   => $user->office_unit,
                    'role_id'       => $user->role_id,
                    'role'          => $user->role?->name,
                    'is_active'     => $user->is_active,
                    'profile_image' => $user->profile_image ?? null,
                    'module_permissions' => $user->modulePermissionsMap(),
                ],
            ],
        ], 200);
    }

    /**
     * Update the specified resource.
     * Not named update because it conflicts with JsonApiController update method signature
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'errors' => [[
                    'status' => '401',
                    'title'  => 'Unauthenticated',
                    'detail' => 'No authenticated user found.',
                ]],
            ], 401);
        }

        $input = $request->json()->all();
        $attributes = $input['data']['attributes'] ?? [];

        $validated = Validator::make($attributes, [
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email'],
            'username' => ['sometimes', 'string'],
            'badge_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'regex:/^63[9]\d{9}$/'],
            'position' => ['sometimes', 'nullable', 'string', 'max:255'],
            'office_unit' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile_image' => ['sometimes', 'nullable', 'string'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => ['required_with:password', 'string'],
        ])->validate();

        $fillable = [
            'name',
            'email',
            'username',
            'badge_number',
            'contact_number',
            'position',
            'office_unit',
            'profile_image',
        ];

        $updateData = array_intersect_key($validated, array_flip($fillable));
        $passwordChanged = false;

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'The current password is incorrect.',
                    'errors' => [
                        'current_password' => [
                            'The current password is incorrect.',
                        ],
                    ],
                ], 422);
            }

            $updateData['password'] = $validated['password'];
            $passwordChanged = true;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
            $user->refresh();
        }

        return response()->json([
            'message' => $passwordChanged
                ? 'Profile updated and password changed successfully.'
                : 'Profile updated successfully.',
            'meta' => [
                'password_changed' => $passwordChanged,
            ],
            'data' => [
                'type'       => 'users',
                'id'         => (string) $user->id,
                'attributes' => [
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    'badge_number'  => $user->badge_number,
                    'contact_number'=> $user->contact_number,
                    'position'      => $user->position,
                    'office_unit'   => $user->office_unit,
                    'role_id'       => $user->role_id,
                    'role'          => $user->role?->name,
                    'is_active'     => $user->is_active,
                    'profile_image' => $user->profile_image ?? null,
                    'module_permissions' => $user->modulePermissionsMap(),
                ],
            ],
        ], 200);
    }
}
