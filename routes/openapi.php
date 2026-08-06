<?php

use App\OpenApi\Controllers\LicenseLeaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:10,1', 'openapi.auth'])->group(function (): void {
    Route::post('/v1/leases/valid', [LicenseLeaseController::class, 'valid'])->name('openapi.license.lease.valid');
});
