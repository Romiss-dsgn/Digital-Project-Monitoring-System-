<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use LaravelJsonApi\Core\Document\Error;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * Issue a Passport token directly instead of proxying to /oauth/token.
     * An internal sub-request deadlocks under `php artisan serve`, which is
     * single-threaded and cannot handle a nested HTTP dispatch.
     *
     * @param \App\Http\Requests\Api\V2\Auth\LoginRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\LaravelJsonApi\Core\Document\Error|\Illuminate\Http\JsonResponse
     */
    public function __invoke(LoginRequest $request): Response|Error|JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user?->is_active) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'detail' => 'This account is pending administrator approval.',
                'status' => Response::HTTP_FORBIDDEN,
            ]);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                'detail' => 'These credentials do not match our records.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        $tokenResult = $user->createToken('ConTrackPro Login');

        $user->forceFill(['last_login_at' => now()])->save();

        $expiresIn = $tokenResult->token->expires_at
            ? now()->diffInSeconds($tokenResult->token->expires_at, false)
            : 31536000;

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => max($expiresIn, 0),
            'access_token' => $tokenResult->accessToken,
        ]);
    }
}
