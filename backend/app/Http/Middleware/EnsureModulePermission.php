<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        abort_unless(
            $user->hasModulePermission($module, $action),
            Response::HTTP_FORBIDDEN,
            'You do not have permission to perform this action.'
        );

        return $next($request);
    }
}
