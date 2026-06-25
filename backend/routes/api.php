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
use App\Http\Controllers\Api\V2\ContractManagementController;
use App\Http\Controllers\Api\V2\ProjectAccomplishmentController;
use App\Http\Controllers\Api\V2\MeController;
use App\Http\Controllers\Api\V2\ProjectController;
use App\Http\Controllers\Api\V2\Admin\EngineeringPlanController;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

Route::prefix('v2')->middleware('json.api')->group(function () {
    Route::post('/login', LoginController::class)->name('login');
    Route::post('/logout', LogoutController::class)->middleware('auth:api');
    Route::post('/register', RegisterController::class);
    Route::post('/password-forgot', ForgotPasswordController::class);
    Route::post('/password-reset', ResetPasswordController::class)->name('password.reset');

    Route::middleware('auth:api')->group(function () {
        Route::get('/contract-management/summary', [ContractManagementController::class, 'summary'])
            ->middleware('permission:contracts,view');
        Route::get('/contract-management/options', [ContractManagementController::class, 'options'])
            ->middleware('permission:contracts,view');
        Route::get('/contracts', [ContractManagementController::class, 'index'])
            ->middleware('permission:contracts,view');
        Route::post('/contracts', [ContractManagementController::class, 'store'])
            ->middleware('permission:contracts,create');
        Route::get('/contracts/{contract}', [ContractManagementController::class, 'show'])
            ->middleware('permission:contracts,view');
        Route::patch('/contracts/{contract}', [ContractManagementController::class, 'update'])
            ->middleware('permission:contracts,edit');
        Route::delete('/contracts/{contract}', [ContractManagementController::class, 'destroy'])
            ->middleware('permission:contracts,delete');
        Route::post('/contracts/{contract}/documents', [ContractManagementController::class, 'uploadDocuments'])
            ->middleware('permission:contract_documents,create');
        Route::get('/contract-documents/{document}/download', [ContractManagementController::class, 'downloadDocument'])
            ->middleware('permission:contract_documents,view');
        Route::patch('/contract-documents/{document}/status', [ContractManagementController::class, 'updateDocumentStatus'])
            ->middleware('permission:contract_documents,approve');
        Route::delete('/contract-documents/{document}', [ContractManagementController::class, 'destroyDocument'])
            ->middleware('permission:contract_documents,delete');

        Route::get('/project-accomplishments/summary', [ProjectAccomplishmentController::class, 'summary'])
            ->middleware('permission:project_accomplishments,view');
        Route::get('/project-accomplishments/options', [ProjectAccomplishmentController::class, 'options'])
            ->middleware('permission:project_accomplishments,view');
        Route::get('/project-accomplishments', [ProjectAccomplishmentController::class, 'index'])
            ->middleware('permission:project_accomplishments,view');
        Route::post('/project-accomplishments', [ProjectAccomplishmentController::class, 'store'])
            ->middleware('permission:project_accomplishments,create');
        Route::get('/project-accomplishments/{accomplishment}', [ProjectAccomplishmentController::class, 'show'])
            ->middleware('permission:project_accomplishments,view');
        Route::patch('/project-accomplishments/{accomplishment}', [ProjectAccomplishmentController::class, 'update'])
            ->middleware('permission:project_accomplishments,edit');
        Route::patch('/project-accomplishments/{accomplishment}/validate', [ProjectAccomplishmentController::class, 'validateRecord'])
            ->middleware('permission:project_accomplishments,approve');
        Route::delete('/project-accomplishments/{accomplishment}', [ProjectAccomplishmentController::class, 'destroy'])
            ->middleware('permission:project_accomplishments,delete');
        Route::post('/project-accomplishments/{accomplishment}/documents', [ProjectAccomplishmentController::class, 'uploadDocument'])
            ->middleware('permission:project_accomplishments,create');
        Route::get('/accomplishment-documents/{document}/download', [ProjectAccomplishmentController::class, 'downloadDocument'])
            ->middleware('permission:project_accomplishments,view');
    });

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

        // Projects
        Route::get('/projects',                    [ProjectController::class, 'index']);
        Route::post('/projects',                   [ProjectController::class, 'store']);
        Route::patch('/projects/{project}',        [ProjectController::class, 'update']);
        Route::delete('/projects/{project}',       [ProjectController::class, 'destroy']);
    });
});

JsonApiRoute::server('v2')->prefix('v2')->resources(function (ResourceRegistrar $server) {
    $server->resource('users', JsonApiController::class);
    Route::get('me', [MeController::class, 'readProfile']);
    Route::patch('me', [MeController::class, 'updateProfile']);
});
