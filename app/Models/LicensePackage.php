<?php

namespace App\Models;

use App\Services\LicenseSigner;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $license_id
 * @property string $customer
 * @property string|null $project
 * @property int|null $open_api_credential_id
 * @property string $plan
 * @property list<string> $features
 * @property list<string> $domains
 * @property CarbonImmutable $issued_at
 * @property CarbonImmutable $not_before
 * @property CarbonImmutable $expires_at
 * @property string $token
 * @property CarbonImmutable|null $revoked_at
 * @property string|null $revoked_reason
 * @property CarbonImmutable|null $last_verified_at
 * @property CarbonImmutable|null $heartbeat_abnormal_notified_at
 * @property-read OpenApiCredential|null $openApiCredential
 */
#[Fillable([
    'license_id', 'customer', 'project', 'open_api_credential_id', 'plan', 'features', 'domains', 'issued_at',
    'not_before', 'expires_at', 'token', 'revoked_at', 'revoked_reason', 'last_verified_at',
    'heartbeat_abnormal_notified_at',
])]
class LicensePackage extends Model
{
    /** @return BelongsTo<OpenApiCredential, $this> */
    public function openApiCredential(): BelongsTo
    {
        return $this->belongsTo(OpenApiCredential::class);
    }

    /** @return HasMany<LicenseInstance, $this> */
    public function instances(): HasMany
    {
        return $this->hasMany(LicenseInstance::class);
    }

    /** @return HasMany<LicenseVerificationLog, $this> */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(LicenseVerificationLog::class);
    }

    public function heartbeatStatus(): string
    {
        if ($this->revoked_at !== null || $this->expires_at->isPast()) {
            return 'inactive';
        }
        if ($this->last_verified_at === null) {
            return $this->not_before->addDays((int) config('licensing.heartbeat_abnormal_days', 7))->isPast()
                ? 'never_verified'
                : 'pending';
        }

        return $this->last_verified_at->addDays((int) config('licensing.heartbeat_abnormal_days', 7))->isPast()
            ? 'abnormal'
            : 'normal';
    }

    public function status(): string
    {
        return match (true) {
            $this->revoked_at !== null => 'revoked',
            $this->not_before->isFuture() => 'pending',
            $this->expires_at->isPast() => 'expired',
            default => 'active',
        };
    }

    public function supportsDomain(string $host): bool
    {
        $host = strtolower(trim($host));

        foreach ($this->domains as $domain) {
            $domain = strtolower(trim((string) $domain));
            if ($domain === $host || (str_starts_with($domain, '*.') && str_ends_with($host, substr($domain, 1)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 写入授权书的完整 claims（含 OpenAPI 凭据与核验端点）。
     *
     * @return array<string, mixed>
     */
    public function claims(): array
    {
        $this->loadMissing('openApiCredential');

        $claims = [
            'v' => LicenseSigner::LICENSE_VERSION,
            'license_id' => $this->license_id,
            'customer' => $this->customer,
            'plan' => $this->plan,
            'issued_at' => $this->issued_at->toIso8601String(),
            'not_before' => $this->not_before->toIso8601String(),
            'expires_at' => $this->expires_at->toIso8601String(),
            'features' => $this->features,
            'domains' => $this->domains,
            'endpoints' => [
                'lease_valid' => rtrim((string) config('licensing.public_base_url', config('app.url')), '/')
                    .'/openapi/v1/leases/valid',
            ],
        ];

        if ($this->project !== null && $this->project !== '') {
            $claims['project'] = $this->project;
        }

        if ($this->openApiCredential !== null) {
            $claims['openapi'] = [
                'client_id' => $this->openApiCredential->client_id,
                'client_secret' => $this->openApiCredential->client_secret,
            ];
        }

        return $claims;
    }

    /** 按当前关联凭据重新签发并静默保存授权书。 */
    public function resignCertificate(): void
    {
        $this->loadMissing('openApiCredential');
        $this->forceFill([
            'token' => app(LicenseSigner::class)->signLicense($this->claims()),
        ])->saveQuietly();
    }

    protected static function booted(): void
    {
        static::saving(function (LicensePackage $license): void {
            if ($license->getAttribute('license_id') === null || $license->getAttribute('license_id') === '') {
                $license->setAttribute('license_id', 'LIC-'.now()->format('Y').'-'.Str::upper((string) Str::ulid()));
            }
            if ($license->getAttribute('issued_at') === null) {
                $license->setAttribute('issued_at', now());
            }

            $claims = [
                'license_id', 'customer', 'project', 'open_api_credential_id', 'plan',
                'issued_at', 'not_before', 'expires_at', 'features', 'domains',
            ];
            if (! $license->exists || $license->isDirty($claims)) {
                $license->loadMissing('openApiCredential');
                $license->token = app(LicenseSigner::class)->signLicense($license->claims());
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'domains' => 'array',
            'issued_at' => 'immutable_datetime',
            'not_before' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'last_verified_at' => 'immutable_datetime',
            'heartbeat_abnormal_notified_at' => 'immutable_datetime',
        ];
    }
}
