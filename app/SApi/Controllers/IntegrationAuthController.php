<?php

namespace App\SApi\Controllers;

use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\User;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntegrationAuthController extends Controller
{
    public function check(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['user_uuid' => 'required|uuid', 'enterprise_uuid' => 'required|uuid', 'employment_uuid' => 'required|uuid', 'application_code' => 'required|string|max:50']);

        return $this->success($access->check($request->attributes->get('sapi_application'), $data));
    }

    /** 用户只能为自己的已授权任职创建票据，平台管理员账号不能冒充职工。 */
    public function ticket(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'application_code' => 'required|string|max:50', 'redirect_uri' => 'required|url|max:1000', 'state' => 'required|string|min:16|max:191']);
        $user = $request->user('api');
        abort_unless($user instanceof User, 401, '需要真实用户会话');
        $app = PlatformApplication::query()->where('code', $data['application_code'])->where('is_enabled', true)->firstOrFail();
        $uris = json_decode($app->getRawOriginal('redirect_uris') ?: '[]', true);
        abort_unless(in_array($data['redirect_uri'], $uris, true), 422, '回跳地址未获授权');
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $employee = PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->where('user_id', $user->id)->firstOrFail();
        $identity = ['user_uuid' => $user->uuid, 'enterprise_uuid' => $enterprise->uuid, 'employment_uuid' => $employee->uuid, 'application_code' => $app->code];
        abort_unless($access->check($app, $identity)['active'], 403, '当前任职无应用访问权限');
        $code = Str::random(64);
        DB::table('platform_login_tickets')->insert(['code_hash' => hash('sha256', $code), 'application_id' => $app->id, 'employee_id' => $employee->id,
            'redirect_uri' => $data['redirect_uri'], 'state' => $data['state'], 'expires_at' => now()->addSeconds(60)]);
        $access->audit($app, 'login.ticket', $enterprise->id, 200, $user->id);

        return $this->success(['code' => $code, 'expires_in' => 60, 'state' => $data['state']]);
    }

    public function exchange(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string|max:191', 'redirect_uri' => 'required|url|max:1000']);
        $app = $request->attributes->get('sapi_application');
        $result = DB::transaction(function () use ($data, $app, $access): array {
            $hash = hash('sha256', $data['code']);
            $ticket = DB::table('platform_login_tickets')->where('code_hash', $hash)->where('application_id', $app->id)->lockForUpdate()->first();
            abort_unless($ticket && ! $ticket->consumed_at && $ticket->expires_at > now() && hash_equals($ticket->redirect_uri, $data['redirect_uri']), 401, '票据无效或已过期');
            $employee = PlatformEmployee::query()->whereKey($ticket->employee_id)->firstOrFail();
            $enterprise = PlatformEnterprise::query()->whereKey($employee->enterprise_id)->firstOrFail();
            $identity = ['user_uuid' => $employee->user->uuid, 'enterprise_uuid' => $enterprise->uuid, 'employment_uuid' => $employee->uuid, 'application_code' => $app->code];
            $checked = $access->check($app, $identity);
            abort_unless($checked['active'], 403, '应用授权已撤销');
            $count = DB::table('platform_login_tickets')->where('code_hash', $hash)->whereNull('consumed_at')->update(['consumed_at' => now()]);
            abort_unless($count === 1, 401, '票据已消费');

            return [...$checked, 'state' => $ticket->state];
        });

        return $this->success($result);
    }

    /** 必须同时具备受限管理应用签名与真实企业管理员用户会话。 */
    public function grant(Request $request, string $enterpriseUuid, string $applicationCode, IntegrationAccess $access, ?string $employmentUuid = null): JsonResponse
    {
        $data = $request->validate(['is_enabled' => 'required|boolean']);
        $caller = $request->attributes->get('sapi_application');
        $enterprise = $access->enterprise($enterpriseUuid);
        $access->require($caller, 'grants.manage', $enterprise->id);
        abort_unless($request->user() && $enterprise->admin_user_id === $request->user()->id, 403, '需要企业管理员授权');
        $app = PlatformApplication::query()->where('code', $applicationCode)->firstOrFail();
        $employee = $employmentUuid ? PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->where('uuid', $employmentUuid)->firstOrFail() : null;
        $version = DB::transaction(function () use ($enterprise, $app, $employee, $data): int {
            $keys = ['enterprise_id' => $enterprise->id, 'application_id' => $app->id, 'employee_id' => $employee === null ? 0 : $employee->id];
            DB::table('platform_application_grants')->insertOrIgnore([...$keys, 'is_enabled' => false, 'version' => 0, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('platform_application_grants')->where($keys)->update(['is_enabled' => $data['is_enabled'], 'version' => DB::raw('version + 1'), 'updated_at' => now()]);

            return (int) DB::table('platform_application_grants')->where($keys)->value('version');
        });
        $access->audit($caller, 'application.grant', $enterprise->id, 200, $request->user()->id, $app->code.':'.($employee === null ? 'enterprise' : $employee->uuid));

        return $this->success(['is_enabled' => $data['is_enabled'], 'version' => $version]);
    }

    public function status(Request $request, IntegrationAccess $access): JsonResponse
    {
        $app = $request->attributes->get('sapi_application');
        $access->require($app, 'platform.admin');

        return $this->success(['applications' => PlatformApplication::query()->select(['id', 'code', 'is_enabled'])->get(),
            'recent_failures' => DB::table('platform_integration_audits')->where('status', '>=', 400)->latest('id')->limit(50)->get(),
            'source_count' => DB::table('platform_directory_sources')->count(), 'change_cursor' => (int) DB::table('platform_directory_changes')->max('id')]);
    }
}
