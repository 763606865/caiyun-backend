<?php

namespace App\OpenApi\Controllers;

use App\Models\LicensePackage;
use App\Services\LicenseSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseLeaseController extends Controller
{
    /**
     * 校验甲方项目授权并签发与当前安装实例绑定的短期租约。
     *
     * POST /openapi/v1/leases/valid
     *
     * @param Request $request
     * @param LicenseSigner $signer
     * @return JsonResponse
     * @throws \Exception
     */
    public function valid(Request $request, LicenseSigner $signer): JsonResponse
    {
        /** @var array{license: string, license_id: string, instance_id: string, domain: string, project?: string|null, app_version?: string|null} $data */
        $data = $request->validate([
            'license' => ['required', 'string', 'max:20000'],
            'license_id' => ['required', 'string', 'max:80'],
            'instance_id' => ['required', 'uuid'],
            'domain' => ['required', 'string', 'max:253'],
            'project' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        // 授权编号用于定位记录，完整签名授权包用于确认调用方持有有效证书。
        $license = LicensePackage::query()->where('license_id', $data['license_id'])->first();

        if (! $license || ! hash_equals($license->token, $data['license'])) {
            return $this->reject('invalid', '授权证书无效');
        }

        // 撤销优先于有效期判断，以便客户端获得明确且不可离线降级的状态。
        if ($license->revoked_at !== null) {
            return $this->reject('revoked', $license->revoked_reason ?: '授权已撤销');
        }
        if ($license->not_before->isFuture() || $license->expires_at->isPast()) {
            return $this->reject('invalid_period', '授权不在有效期内');
        }
        if (! $license->supportsDomain($data['domain'])) {
            return $this->reject('domain_mismatch', '当前域名未获得授权');
        }
        if ($license->project !== null && $license->project !== ($data['project'] ?? null)) {
            return $this->reject('project_mismatch', '授权项目不匹配');
        }

        $issuedAt = now();
        // 租约有效期取配置时长与授权包剩余有效期中的较小值，绝不越过授权到期时间。
        $expiresAt = $issuedAt->copy()->addHours(max(1, (int) config('licensing.lease_hours', 72)));
        if ($expiresAt->isAfter($license->expires_at)) {
            $expiresAt = $license->expires_at;
        }

        $signedLease = $signer->sign([
            'license_id' => $license->license_id,
            'instance_id' => $data['instance_id'],
            'domain' => strtolower($data['domain']),
            'issued_at' => $issuedAt->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'features' => $license->features,
        ]);

        // 该字段仅记录在线校验情况，不触发模型重新签发授权包。
        $license->forceFill(['last_verified_at' => $issuedAt])->saveQuietly();

        return $this->success(['lease' => $signedLease]);
    }

    /**
     * 返回统一的授权拒绝响应，同时保留供客户端程序判断的 reason 机器码。
     */
    private function reject(string $reason, string $message): JsonResponse
    {
        return $this->error($message, 403, reason: $reason);
    }
}
