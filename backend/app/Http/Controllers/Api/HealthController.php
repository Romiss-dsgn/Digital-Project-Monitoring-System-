<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            return response()->json([
                'status' => 'unhealthy',
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'database' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'organization' => config('app.organization_name'),
            'jurisdiction' => config('app.region'),
            'environment' => app()->environment(),
            'database' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
