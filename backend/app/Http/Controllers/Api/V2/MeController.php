<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $updateData = array_intersect_key($attributes, array_flip($fillable));

        if (!empty($updateData)) {
            $user->update($updateData);
            $user->refresh();
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
                ],
            ],
        ], 200);
    }
}
