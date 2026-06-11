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

        $request = Request::create(config('app.url') . '/oauth/token', 'POST', [
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
            return Error::fromArray([
                'title'  => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'detail' => $response->exception->getMessage(),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $response;
    }
}
