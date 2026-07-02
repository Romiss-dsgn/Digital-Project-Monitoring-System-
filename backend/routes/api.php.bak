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
use App\Http\Controllers\Api\V2\CashflowPeriodController;
use App\Http\Controllers\Api\V2\InvoiceController;
use App\Http\Controllers\Api\V2\VariationOrderController;
use App\Http\Controllers\Api\V2\ProjectAccomplishmentController;
use App\Http\Controllers\Api\V2\ProjectController;
use App\Http\Controllers\Api\V2\Admin\EngineeringPlanController;
use App\Http\Controllers\Api\V2\MeController;
use App\Http\Controllers\Api\AuditLogController;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;

Route::prefix("v2")->middleware("json.api")->group(function () {
    Route::post("/login", LoginController::class)->name("login");
    Route::post("/logout", LogoutController::class)->middleware("auth:api");
    Route::post("/register", RegisterController::class);
    Route::post("/password-forgot", ForgotPasswordController::class);
    Route::post("/password-reset", ResetPasswordController::class)->name("password.reset");

    Route::middleware("auth:api")->group(function () {
        Route::get("/contract-management/summary", [ContractManagementController::class, "summary"])
            ->middleware("permission:contracts,view");
        Route::get("/contract-management/options", [ContractManagementController::class, "options"])
            ->middleware("permission:contracts,view");
        Route::get("/contracts", [ContractManagementController::class, "index"])
            ->middleware("permission:contracts,view");
        Route::post("/contracts", [ContractManagementController::class, "store"])
            ->middleware("permission:contracts,create");
        Route::get("/contracts/{contract}", [ContractManagementController::class, "show"])
            ->middleware("permission:contracts,view");
        Route::patch("/contracts/{contract}", [ContractManagementController::class, "update"])
            ->middleware("permission:contracts,edit");
        Route::delete("/contracts/{contract}", [ContractManagementController::class, "destroy"])
            ->middleware("permission:contracts,delete");
        Route::post("/contracts/{contract}/documents", [ContractManagementController::class, "uploadDocuments"])
            ->middleware("permission:contract_documents,create");
        Route::get("/contract-documents/{document}/download", [ContractManagementController::class, "downloadDocument"])
            ->middleware("permission:contract_documents,view");
        Route::patch("/contract-documents/{document}/status", [ContractManagementController::class, "updateDocumentStatus"])
            ->middleware("permission:contract_documents,approve");
        Route::delete("/contract-documents/{document}", [ContractManagementController::class, "destroyDocument"])
            ->middleware("permission:contract_documents,delete");

        Route::get("/project-accomplishments/summary", [ProjectAccomplishmentController::class, "summary"])
            ->middleware("permission:project_accomplishments,view");
        Route::get("/project-accomplishments/options", [ProjectAccomplishmentController::class, "options"])
            ->middleware("permission:project_accomplishments,view");
        Route::get("/project-accomplishments", [ProjectAccomplishmentController::class, "index"])
            ->middleware("permission:project_accomplishments,view");
        Route::post("/project-accomplishments", [ProjectAccomplishmentController::class, "store"])
            ->middleware("permission:project_accomplishments,create");
        Route::get("/project-accomplishments/{accomplishment}", [ProjectAccomplishmentController::class, "show"])
            ->middleware("permission:project_accomplishments,view");
        Route::patch("/project-accomplishments/{accomplishment}", [ProjectAccomplishmentController::class, "update"])
            ->middleware("permission:project_accomplishments,edit");
        Route::patch("/project-accomplishments/{accomplishment}/validate", [ProjectAccomplishmentController::class, "validateRecord"])
            ->middleware("permission:project_accomplishments,approve");
        Route::delete("/project-accomplishments/{accomplishment}", [ProjectAccomplishmentController::class, "destroy"])
            ->middleware("permission:project_accomplishments,delete");
        Route::post("/project-accomplishments/{accomplishment}/documents", [ProjectAccomplishmentController::class, "uploadDocument"])
            ->middleware("permission:project_accomplishments,create");
        Route::get("/accomplishment-documents/{document}/download", [ProjectAccomplishmentController::class, "downloadDocument"])
            ->middleware("permission:project_accomplishments,view");

        // Cashflow Periods
        Route::get('/cashflow-periods/summary', [CashflowPeriodController::class, 'summary'])
            ->middleware('permission:cashflow_periods,view');
        Route::get('/cashflow-periods/options', [CashflowPeriodController::class, 'options'])
            ->middleware('permission:cashflow_periods,view');
        Route::get('/cashflow-periods', [CashflowPeriodController::class, 'index'])
            ->middleware('permission:cashflow_periods,view');
        Route::post('/cashflow-periods', [CashflowPeriodController::class, 'store'])
            ->middleware('permission:cashflow_periods,create');
        Route::get('/cashflow-periods/{cashflowPeriod}', [CashflowPeriodController::class, 'show'])
            ->middleware('permission:cashflow_periods,view');
        Route::patch('/cashflow-periods/{cashflowPeriod}', [CashflowPeriodController::class, 'update'])
            ->middleware('permission:cashflow_periods,edit');
        Route::delete('/cashflow-periods/{cashflowPeriod}', [CashflowPeriodController::class, 'destroy'])
            ->middleware('permission:cashflow_periods,delete');
        Route::get('/cashflow-periods/{cashflowPeriod}/invoices', [CashflowPeriodController::class, 'getInvoices'])
            ->middleware('permission:invoices,view');

        // Invoices
        Route::get('/invoices/summary', [InvoiceController::class, 'summary'])
            ->middleware('permission:invoices,view');
        Route::get('/invoices/options', [InvoiceController::class, 'options'])
            ->middleware('permission:invoices,view');
        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->middleware('permission:invoices,view');
        Route::post('/invoices', [InvoiceController::class, 'store'])
            ->middleware('permission:invoices,create');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
            ->middleware('permission:invoices,view');
        Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update'])
            ->middleware('permission:invoices,edit');
        Route::patch('/invoices/{invoice}/verify', [InvoiceController::class, 'verify'])
            ->middleware('permission:invoices,create');
        Route::patch('/invoices/{invoice}/approve', [InvoiceController::class, 'approve'])
            ->middleware('permission:invoices,approve');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])
            ->middleware('permission:invoices,delete');
        Route::post('/invoices/{invoice}/documents', [InvoiceController::class, 'uploadDocument'])
            ->middleware('permission:invoices,create');
        Route::get('/invoice-documents/{document}/download', [InvoiceController::class, 'downloadDocument'])
            ->middleware('permission:invoices,view');

        // Payments
        Route::post('/payments', [\App\Http\Controllers\Api\V2\PaymentController::class, 'store'])
            ->middleware('permission:invoices,create');

        // Variation Orders
        Route::get('/variation-orders/summary', [VariationOrderController::class, 'summary'])
            ->middleware('permission:variation_orders,view');
        Route::get('/variation-orders/options', [VariationOrderController::class, 'options'])
            ->middleware('permission:variation_orders,view');
        Route::get('/variation-orders', [VariationOrderController::class, 'index'])
            ->middleware('permission:variation_orders,view');
        Route::post('/variation-orders', [VariationOrderController::class, 'store'])
            ->middleware('permission:variation_orders,create');
        Route::get('/variation-orders/{variationOrder}', [VariationOrderController::class, 'show'])
            ->middleware('permission:variation_orders,view');
        Route::patch('/variation-orders/{variationOrder}', [VariationOrderController::class, 'update'])
            ->middleware('permission:variation_orders,edit');
        Route::patch('/variation-orders/{variationOrder}/submit', [VariationOrderController::class, 'submit'])
            ->middleware('permission:variation_orders,create');
        Route::patch('/variation-orders/{variationOrder}/review', [VariationOrderController::class, 'review'])
            ->middleware('permission:variation_orders,approve');
        Route::delete('/variation-orders/{variationOrder}', [VariationOrderController::class, 'destroy'])
            ->middleware('permission:variation_orders,delete');
        Route::post('/variation-orders/{variationOrder}/documents', [VariationOrderController::class, 'uploadDocument'])
            ->middleware('permission:variation_orders,create');
        Route::get('/variation-order-documents/{document}/download', [VariationOrderController::class, 'downloadDocument'])
            ->middleware('permission:variation_orders,view');
    });

    Route::middleware("auth:api")->prefix("admin")->group(function () {
        Route::get("/access-requests", [UserAccessController::class, "index"]);
        Route::patch("/access-requests/{accessRequest}/approve", [UserAccessController::class, "approve"]);
        Route::patch("/access-requests/{accessRequest}/reject", [UserAccessController::class, "reject"]);

        Route::get("/users/stats",              [UserManagementController::class, "stats"]);
        Route::get("/users",                    [UserManagementController::class, "index"]);
        Route::post("/users",                   [UserManagementController::class, "store"]);
        Route::get("/users/{user}",             [UserManagementController::class, "show"]);
        Route::put("/users/{user}",             [UserManagementController::class, "update"]);
        Route::patch("/users/{user}/status",    [UserManagementController::class, "updateStatus"]);
        Route::delete("/users/{user}",          [UserManagementController::class, "destroy"]);

        Route::post("/users/{user}/accept",     [UserManagementController::class, "accept"]);
        Route::delete("/users/{user}/reject",   [UserManagementController::class, "reject"]);

        Route::get("/roles",                    [UserManagementController::class, "roles"]);

        // Engineering plans
        Route::get("/engineering-plans",        [EngineeringPlanController::class, "index"]);
        Route::post("/engineering-plans",       [EngineeringPlanController::class, "store"]);
        Route::get("/engineering-plans/{engineeringPlan}", [EngineeringPlanController::class, "show"]);
        Route::get("/engineering-plans/{engineeringPlan}/download", [EngineeringPlanController::class, "download"]);
        Route::patch("/engineering-plans/{engineeringPlan}/status", [EngineeringPlanController::class, "updateStatus"]);
        Route::delete("/engineering-plans/{engineeringPlan}", [EngineeringPlanController::class, "destroy"]);

        // Projects
        Route::get("/projects",                 [ProjectController::class, "index"]);
        Route::post("/projects",                [ProjectController::class, "store"]);
        Route::patch("/projects/{project}",     [ProjectController::class, "update"]);
        Route::delete("/projects/{project}",    [ProjectController::class, "destroy"]);

        // Audit Logs
        Route::get("/audit-logs",               [AuditLogController::class, "index"]);
        Route::get("/audit-logs/stats",         [AuditLogController::class, "stats"]);
        Route::get("/audit-logs/modules",       [AuditLogController::class, "modules"]);
        Route::get("/audit-logs/roles",         [AuditLogController::class, "roles"]);
        Route::post("/audit-logs/export",       [AuditLogController::class, "export"]);
    });
});

JsonApiRoute::server("v2")->prefix("v2")->resources(function (ResourceRegistrar $server) {
    $server->resource("users", JsonApiController::class);
    Route::get("me", [MeController::class, "readProfile"]);
    Route::patch("me", [MeController::class, "updateProfile"]);
});
