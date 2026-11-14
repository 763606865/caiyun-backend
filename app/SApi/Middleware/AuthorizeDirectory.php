<?php

namespace App\SApi\Middleware;

use App\Models\PlatformEnterprise;
use App\Services\Platform\IntegrationAccess;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/** 旧 CRUD 仅限显式授予平台运维权限的凭证；普通业务应用走企业范围的目录接口。 */
class AuthorizeDirectory
{
    public function handle(Request $request, Closure $next): Response
    {
        $access = app(IntegrationAccess::class);
        $application = $request->attributes->get('sapi_application');
        try {
            if ($request->is('sapi/enterprises/*/subscriptions*') && ! $access->allows($application, 'platform.admin')) {
                $key = (string) $request->route('enterprise');
                $company = ctype_digit($key) ? PlatformEnterprise::query()->findOrFail((int) $key) : $access->enterprise($key);
                $access->require($application, $request->isMethod('GET') ? 'subscription.read' : 'subscription.write', $company->id);
                $request->route()->setParameter('enterprise', (string) $company->id);
                if ($request->route('id')) {
                    abort_unless(DB::table('platform_subscriptions')->where('id', $request->route('id'))->where('enterprise_id', $company->id)->where('application_id', $application->id)->exists(), 404);
                }
                abort_if($request->filled('application_id') && (int) $request->input('application_id') !== $application->id, 403);
                abort_if($request->filled('application_code') && $request->input('application_code') !== $application->code, 403);
                if ($request->isMethod('POST')) {
                    $request->merge(['application_id' => $application->id, 'application_code' => $application->code]);
                }
            } elseif (! $request->is('sapi/enterprises/*/application-grants/*', 'sapi/enterprises/*/employments/*/application-grants/*', 'sapi/ping', 'sapi/directory/*', 'sapi/identity/*', 'sapi/auth/*', 'sapi/integration/*')) {
                $access->require($application, 'platform.admin');
            }
            $response = $next($request);
        } catch (\Throwable $e) {
            $access->audit($application, $request->method().' '.$request->path(), null, match (true) {
                $e instanceof ValidationException => 422, $e instanceof ModelNotFoundException => 404, method_exists($e, 'getStatusCode') => $e->getStatusCode(), default => 500
            });
            throw $e;
        }
        $access->audit($application, $request->method().' '.$request->path(), null, $response->getStatusCode());

        return $response;
    }
}
