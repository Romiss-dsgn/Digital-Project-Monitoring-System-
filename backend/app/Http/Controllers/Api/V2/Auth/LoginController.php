<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\LoginRequest;
use LaravelJsonApi\Core\Document\Error;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \App\Http\Requests\Api\V2\Auth\LoginRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\LaravelJsonApi\Core\Document\Error
     * @throws \Exception
     */
    public function __invoke(LoginRequest $request): Response|Error
    {
        $user = User::where('email', $request->email)->first();

        if (!$user?->is_active) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'detail' => 'This account is pending administrator approval.',
                'status' => Response::HTTP_FORBIDDEN,
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                'detail' => 'The provided credentials are incorrect.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $user->createToken('ConTrackPro API Token')->accessToken,
        ]);
    }
}
