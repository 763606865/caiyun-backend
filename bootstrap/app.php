<?php

use App\Admin\Commands\CreateAdminUser;
use App\Admin\Commands\GenerateLicenseKeys;
use App\Admin\Commands\InstallCms;
use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RecordClientContext;
use App\OpenApi\Middleware\VerifyEncryptedOpenApiRequest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::prefix('openapi')->group(base_path('routes/openapi.php'));
        },
    )
    ->withCommands([
        CreateAdminUser::class,
        InstallCms::class,
        GenerateLicenseKeys::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'openapi.auth' => VerifyEncryptedOpenApiRequest::class,
        ]);

        $middleware->api(prepend: [
            RecordClientContext::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'openapi/*') || $request->expectsJson(),
        );

        $exceptions->render(new ApiExceptionRenderer);
    })->create();
