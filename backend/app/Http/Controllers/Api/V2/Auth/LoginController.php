<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Auth\LoginRequest;
use LaravelJsonApi\Core\Document\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        if (!$client?->id || !$client?->secret) {
            return Error::fromArray([
                'title' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                'detail' => 'Passport password client is not configured. Run php artisan passport:client --password inside the backend container.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $request->email,
            'password'      => $request->password,
            'scope'         => '',
        ]);

        /** @var \Illuminate\Http\Response $response */
        $response = app()->handle($request);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $payload = json_decode($response->getContent(), true) ?: [];
            $detail = $payload['error_description']
                ?? $payload['message']
                ?? $payload['error']
                ?? $response->exception?->getMessage()
                ?? 'Authentication failed.';
            $status = $response->getStatusCode();

            return Error::fromArray([
                'title'  => Response::$statusTexts[$status] ?? Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'detail' => $detail,
                'status' => $status,
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $response;
    }
}
