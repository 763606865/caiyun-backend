<?php

namespace App\Api\Controllers;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * 同步当前客户端安装实例的设备及版本信息。
     *
     * POST /api/devices/sync
     */
    public function sync(Request $request): JsonResponse
    {
        /** @var array<string, string> $context */
        $context = $request->attributes->get('client_context');
        $authenticated = Auth::guard('api')->user();
        $user = $authenticated instanceof User ? $authenticated : null;
        $now = now();

        if ($user !== null) {
            $request->setUserResolver(fn (): User => $user);
        }

        $values = [
            'user_id' => $user?->id,
            'device_id' => $context['device_id'],
            'client_type' => $context['client_type'],
            'app_version' => $context['app_version'],
            'app_build' => $context['app_build'],
            'platform' => $context['platform'],
            'os_version' => $context['os_version'],
            'channel' => $context['channel'],
            'last_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $updateColumns = [
            'client_type',
            'app_version',
            'app_build',
            'platform',
            'os_version',
            'channel',
            'last_ip',
            'user_agent',
            'last_seen_at',
            'updated_at',
        ];

        // 匿名同步不能清除设备之前建立的用户关联。
        if ($user !== null) {
            $updateColumns[] = 'user_id';
        }

        UserDevice::query()->upsert(
            [$values],
            ['device_id'],
            $updateColumns,
        );

        return $this->success([
            'message' => '设备信息同步成功',
            'device' => [
                'device_id' => $context['device_id'],
                'client_type' => $context['client_type'],
                'app_version' => $context['app_version'],
                'app_build' => $context['app_build'],
                'platform' => $context['platform'],
                'os_version' => $context['os_version'],
                'channel' => $context['channel'],
                'synced_at' => $now,
            ],
        ]);
    }
}
