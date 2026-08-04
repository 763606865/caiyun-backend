<?php

namespace App\Api\Controllers;

use App\Models\LicensePackage;
use App\Services\LicenseSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseLeaseController
{
    public function __invoke(Request $request, LicenseSigner $signer): JsonResponse
    {
        $data = $request->validate([
            'license' => ['required', 'string', 'max:20000'],
            'license_id' => ['required', 'string', 'max:80'],
            'instance_id' => ['required', 'uuid'],
            'domain' => ['required', 'string', 'max:253'],
            'project' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        $license = LicensePackage::query()->where('license_id', $data['license_id'])->first();
        if (! $license || ! hash_equals($license->token, $data['license'])) {
            return response()->json(['reason' => 'invalid', 'message' => '授权证书无效'], 403);
        }
        if ($license->revoked_at !== null) {
            return response()->json(['reason' => 'revoked', 'message' => $license->revoked_reason ?: '授权已撤销'], 403);
        }
        if ($license->not_before->isFuture() || $license->expires_at->isPast()) {
            return response()->json(['reason' => 'invalid_period', 'message' => '授权不在有效期内'], 403);
        }
        if (! $license->supportsDomain($data['domain'])) {
            return response()->json(['reason' => 'domain_mismatch', 'message' => '当前域名未获得授权'], 403);
        }
        if ($license->project !== null && $license->project !== ($data['project'] ?? null)) {
            return response()->json(['reason' => 'project_mismatch', 'message' => '授权项目不匹配'], 403);
        }

        $issuedAt = now();
        $expiresAt = $issuedAt->addHours(max(1, (int) config('licensing.lease_hours', 72)));
        if ($expiresAt->isAfter($license->expires_at)) {
            $expiresAt = $license->expires_at;
        }
        $lease = $signer->sign([
            'license_id' => $license->license_id,
            'instance_id' => $data['instance_id'],
            'domain' => strtolower($data['domain']),
            'issued_at' => $issuedAt->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'features' => $license->features,
        ]);
        $license->forceFill(['last_verified_at' => $issuedAt])->saveQuietly();

        return response()->json(['lease' => $lease]);
    }
}
