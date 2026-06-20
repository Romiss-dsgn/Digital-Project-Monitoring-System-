<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;
use App\Http\Controllers\Api\V2\Admin\UserAccessController;
use App\Http\Controllers\Api\V2\Admin\UserManagementController;
use App\Http\Controllers\Api\V2\Auth\LoginController;
use App\Http\Controllers\Api\V2\Auth\LogoutController;
use App\Http\Controllers\Api\V2\Auth\RegisterController;
use App\Http\Controllers\Api\V2\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V2\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V2\MeController;
use App\Http\Controllers\Api\V2\Admin\EngineeringPlanController;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

Route::prefix('v2')->middleware('json.api')->group(function () {
    Route::post('/login', LoginController::class)->name('login');
    Route::post('/logout', LogoutController::class)->middleware('auth:api');
    Route::post('/register', RegisterController::class);
    Route::post('/password-forgot', ForgotPasswordController::class);
    Route::post('/password-reset', ResetPasswordController::class)->name('password.reset');

    Route::middleware('auth:api')->prefix('admin')->group(function () {
        Route::get('/access-requests', [UserAccessController::class, 'index']);
        Route::patch('/access-requests/{accessRequest}/approve', [UserAccessController::class, 'approve']);
        Route::patch('/access-requests/{accessRequest}/reject', [UserAccessController::class, 'reject']);

        Route::get('/users/stats',              [UserManagementController::class, 'stats']);
        Route::get('/users',                    [UserManagementController::class, 'index']);
        Route::post('/users',                   [UserManagementController::class, 'store']);
        Route::get('/users/{user}',             [UserManagementController::class, 'show']);
        Route::put('/users/{user}',             [UserManagementController::class, 'update']);
        Route::patch('/users/{user}/status',    [UserManagementController::class, 'updateStatus']);
        Route::delete('/users/{user}',          [UserManagementController::class, 'destroy']);

        // NEW: Accept / Reject pending users
        Route::post('/users/{user}/accept',     [UserManagementController::class, 'accept']);
        Route::delete('/users/{user}/reject',   [UserManagementController::class, 'reject']);

        Route::get('/roles',                    [UserManagementController::class, 'roles']);
        
        // Engineering plans
        Route::post('/engineering-plans',       [EngineeringPlanController::class, 'store']);
    });
});

JsonApiRoute::server('v2')->prefix('v2')->resources(function (ResourceRegistrar $server) {
    $server->resource('users', JsonApiController::class);
    Route::get('me', [MeController::class, 'readProfile']);
    Route::patch('me', [MeController::class, 'updateProfile']);
});