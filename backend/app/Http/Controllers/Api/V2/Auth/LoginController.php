<?php
namespace App\Http\Controllers\Api\V2\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use LaravelJsonApi\Core\Document\Error;
use Symfony\Component\HttpFoundation\Response;

use Carbon\Carbon;
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

        // Wrong credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return Error::fromArray([
                'title'  => Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                'detail' => 'These credentials do not match our records.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                'detail' => 'Passport password client is not configured. Run php artisan passport:client --password inside the backend container.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }

        // Pending — not yet accepted by admin
        if (!$user->is_active && is_null($user->accepted_at)) {
            return Error::fromArray([
                'title'  => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'detail' => 'Your account is pending administrator approval. Please wait for confirmation.',
                'status' => Response::HTTP_FORBIDDEN,
            ]);
        }

        // Accepted but first login (is_active=false, accepted_at is set)
        // → activate the account now
        if (!$user->is_active && !is_null($user->accepted_at)) {
            $user->forceFill([
                'is_active'      => true,
                'last_active_at' => Carbon::now(),
                'last_login_at'  => Carbon::now(),
            ])->save();
        } else {
            // Already active — just update timestamps
            $user->forceFill([
                'last_active_at' => Carbon::now(),
                'last_login_at'  => Carbon::now(),
            ])->save();
        }

        $tokenResult = $user->createToken('ConTrackPro Login');
        $expiresIn = $tokenResult->token->expires_at
            ? now()->diffInSeconds($tokenResult->token->expires_at, false)
            : 31536000;

        return response()->json([
            'token_type'   => 'Bearer',
            'expires_in'   => max($expiresIn, 0),
            'access_token' => $tokenResult->accessToken,
        ]);
    }
}
