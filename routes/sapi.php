<?php

use App\SApi\Controllers\EmployeeController;
use App\SApi\Controllers\EnterpriseController;
use App\SApi\Controllers\OrganizationController;
use App\SApi\Controllers\PingController;
use App\SApi\Controllers\SubscriptionController;
use App\SApi\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('ping', PingController::class)->name('sapi.ping');
Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
Route::apiResource('organizations', OrganizationController::class)->parameters(['organizations' => 'id']);
Route::apiResource('enterprises', EnterpriseController::class)->parameters(['enterprises' => 'id']);

Route::apiResource('enterprises.employees', EmployeeController::class)
    ->parameters(['enterprises' => 'enterprise', 'employees' => 'id']);
Route::apiResource('enterprises.subscriptions', SubscriptionController::class)
    ->parameters(['enterprises' => 'enterprise', 'subscriptions' => 'id']);
