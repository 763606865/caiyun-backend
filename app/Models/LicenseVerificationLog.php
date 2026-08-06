<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 授权在线验证审计日志，不保存授权 Token 或 OpenAPI 密钥。 */
#[Table(name: 'license_verification_logs')]
#[Fillable([
    'license_package_id', 'license_instance_id', 'open_api_credential_id', 'requested_license_id',
    'instance_id', 'domain', 'project', 'app_version', 'result', 'reason', 'ip', 'verified_at',
])]
class LicenseVerificationLog extends Model
{
    /** @return BelongsTo<LicensePackage, $this> */
    public function licensePackage(): BelongsTo
    {
        return $this->belongsTo(LicensePackage::class);
    }

    /** @return BelongsTo<LicenseInstance, $this> */
    public function licenseInstance(): BelongsTo
    {
        return $this->belongsTo(LicenseInstance::class);
    }

    /** @return BelongsTo<OpenApiCredential, $this> */
    public function openApiCredential(): BelongsTo
    {
        return $this->belongsTo(OpenApiCredential::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }
}
