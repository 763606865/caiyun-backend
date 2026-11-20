<?php

use App\SApi\Controllers\BusinessExchangeController;
use App\SApi\Controllers\ContractApprovalController;
use App\SApi\Controllers\DirectoryController;
use App\SApi\Controllers\EmployeeController;
use App\SApi\Controllers\EnterpriseController;
use App\SApi\Controllers\IntegrationAuthController;
use App\SApi\Controllers\MasterDataController;
use App\SApi\Controllers\OrganizationController;
use App\SApi\Controllers\PingController;
use App\SApi\Controllers\SubscriptionController;
use App\SApi\Controllers\UserController;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('ping', PingController::class)->name('sapi.ping');
Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
Route::apiResource('organizations', OrganizationController::class)->parameters(['organizations' => 'id']);
Route::apiResource('enterprises', EnterpriseController::class)->parameters(['enterprises' => 'id']);

Route::apiResource('enterprises.employees', EmployeeController::class)
    ->parameters(['enterprises' => 'enterprise', 'employees' => 'id']);
Route::apiResource('enterprises.subscriptions', SubscriptionController::class)
    ->parameters(['enterprises' => 'enterprise', 'subscriptions' => 'id']);

// 第一阶段：签名应用还需企业级 scope，业务凭证不能自行授予权限。
Route::post('directory/upserts', [DirectoryController::class, 'upsert']);
Route::get('directory/snapshots', [DirectoryController::class, 'snapshot']);
Route::get('directory/changes', [DirectoryController::class, 'changes']);
Route::post('identity/resolve', [DirectoryController::class, 'resolve']);
Route::post('auth/check', [IntegrationAuthController::class, 'check']);
Route::post('auth/exchange', [IntegrationAuthController::class, 'exchange']);
Route::post('auth/delegated-tickets', [IntegrationAuthController::class, 'delegate']);
Route::prefix('integration/contract-approvals')->controller(ContractApprovalController::class)->group(function (): void {
    Route::post('/', 'store');
    Route::get('/', 'index');
    Route::get('/{uuid}', 'show');
    Route::post('/{uuid}/result', 'result');
    Route::post('/{uuid}/cancel', 'cancel');
});
Route::get('integration/status', [IntegrationAuthController::class, 'status']);
Route::middleware('auth:api')->group(function (): void {
    Route::put('enterprises/{enterpriseUuid}/application-grants/{applicationCode}', function (Request $request, string $enterpriseUuid, string $applicationCode) {
        return app(IntegrationAuthController::class)->grant($request, $enterpriseUuid, $applicationCode, app(IntegrationAccess::class));
    });
    Route::put('enterprises/{enterpriseUuid}/employments/{employmentUuid}/application-grants/{applicationCode}', function (Request $request, string $enterpriseUuid, string $employmentUuid, string $applicationCode) {
        return app(IntegrationAuthController::class)->grant($request, $enterpriseUuid, $applicationCode, app(IntegrationAccess::class), $employmentUuid);
    });
});

Route::prefix('integration/masters')->controller(MasterDataController::class)->group(function (): void {
    Route::post('/', 'store');
    Route::get('/', 'index');
    Route::post('/resolve', 'resolve');
    Route::post('/{uuid}/bind', 'bind');
});
Route::prefix('integration/messages')->controller(BusinessExchangeController::class)->group(function (): void {
    Route::post('/', 'store');
    Route::get('/', 'index');
    Route::post('/{uuid}/ack', 'ack');
});
