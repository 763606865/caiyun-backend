<?php

namespace App\Services;

use App\Models\LicenseInstance;
use App\Models\LicensePackage;
use App\Models\LicenseVerificationLog;
use App\Models\OpenApiCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseVerificationRecorder
{
    /**
     * @param  array{license_id: string, instance_id: string, domain: string, project?: string|null, app_version?: string|null}  $data
     */
    public function success(LicensePackage $license, array $data, Request $request): LicenseInstance
    {
        return DB::transaction(function () use ($license, $data, $request): LicenseInstance {
            $now = now();
            $credential = $this->credential($request);
            $instance = LicenseInstance::query()->firstOrNew([
                'license_package_id' => $license->id,
                'instance_id' => $data['instance_id'],
            ]);
            if (! $instance->exists) {
                $instance->first_verified_at = $now;
            }
            $instance->fill([
                'open_api_credential_id' => $credential?->id,
                'domain' => strtolower($data['domain']),
                'project' => $data['project'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'status' => 'active',
                'last_verified_at' => $now,
                'last_ip' => $request->ip(),
                'abnormal_notified_at' => null,
            ])->save();

            $license->forceFill([
                'last_verified_at' => $now,
                'heartbeat_abnormal_notified_at' => null,
            ])->saveQuietly();
            $this->log($license, $instance, $credential, $data, $request, 'success');

            return $instance;
        });
    }

    /**
     * @param  array{license_id: string, instance_id: string, domain: string, project?: string|null, app_version?: string|null}  $data
     */
    public function rejected(?LicensePackage $license, array $data, Request $request, string $reason): void
    {
        $this->log($license, null, $this->credential($request), $data, $request, 'rejected', $reason);
    }

    /**
     * @param  array{license_id: string, instance_id: string, domain: string, project?: string|null, app_version?: string|null}  $data
     */
    private function log(
        ?LicensePackage $license,
        ?LicenseInstance $instance,
        ?OpenApiCredential $credential,
        array $data,
        Request $request,
        string $result,
        ?string $reason = null,
    ): void {
        LicenseVerificationLog::query()->create([
            'license_package_id' => $license?->id,
            'license_instance_id' => $instance?->id,
            'open_api_credential_id' => $credential?->id,
            'requested_license_id' => $data['license_id'],
            'instance_id' => $data['instance_id'],
            'domain' => strtolower($data['domain']),
            'project' => $data['project'] ?? null,
            'app_version' => $data['app_version'] ?? null,
            'result' => $result,
            'reason' => $reason,
            'ip' => $request->ip(),
            'verified_at' => now(),
        ]);
    }

    private function credential(Request $request): ?OpenApiCredential
    {
        $credential = $request->attributes->get('openapi_credential');

        return $credential instanceof OpenApiCredential ? $credential : null;
    }
}
