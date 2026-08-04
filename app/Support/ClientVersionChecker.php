<?php

namespace App\Support;

use App\Models\ClientVersion;
use Illuminate\Support\Facades\Cache;

class ClientVersionChecker
{
    /**
     * 检查当前客户端的版本更新策略。
     *
     * @param  array<string, string>  $context
     * @return array{
     *     has_update: bool,
     *     force_update: bool,
     *     below_minimum: bool,
     *     update_type: 'none'|'optional'|'force',
     *     latest_version: array<string, mixed>|null
     * }
     */
    public function check(array $context): array
    {
        $latest = $this->latestVersion(
            $context['client_type'],
            $context['platform'],
            $context['channel'],
        );

        if ($latest === null) {
            return [
                'has_update' => false,
                'force_update' => false,
                'below_minimum' => false,
                'update_type' => 'none',
                'latest_version' => null,
            ];
        }

        $hasUpdate = $this->isOlderThan(
            $context['app_version'],
            $context['app_build'],
            $latest['app_version'],
            $latest['app_build'],
        );
        $belowMinimum = $this->isBelowMinimum($context, $latest);
        $forceUpdate = $hasUpdate && ($latest['is_force_update'] || $belowMinimum);

        return [
            'has_update' => $hasUpdate,
            'force_update' => $forceUpdate,
            'below_minimum' => $belowMinimum,
            'update_type' => $forceUpdate ? 'force' : ($hasUpdate ? 'optional' : 'none'),
            'latest_version' => [
                'app_version' => $latest['app_version'],
                'app_build' => $latest['app_build'],
                'min_supported_version' => $latest['min_supported_version'],
                'min_supported_build' => $latest['min_supported_build'],
                'download_url' => $latest['download_url'],
                'release_notes' => $latest['release_notes'],
                'published_at' => $latest['published_at'],
            ],
        ];
    }

    public function forget(string $clientType, string $platform, string $channel): void
    {
        Cache::forget($this->cacheKey($clientType, $platform, $channel));
    }

    /**
     * @return array{
     *     app_version: string,
     *     app_build: string,
     *     min_supported_version: string|null,
     *     min_supported_build: string|null,
     *     is_force_update: bool,
     *     download_url: string|null,
     *     release_notes: string|null,
     *     published_at: string|null
     * }|null
     */
    private function latestVersion(string $clientType, string $platform, string $channel): ?array
    {
        $cached = Cache::remember(
            $this->cacheKey($clientType, $platform, $channel),
            (int) config('client.version_cache_ttl', 300),
            function () use ($clientType, $platform, $channel): array {
                $version = ClientVersion::query()
                    ->published()
                    ->where('client_type', $clientType)
                    ->where('platform', $platform)
                    ->where('channel', $channel)
                    ->latest('published_at')
                    ->latest('id')
                    ->first();

                if ($version === null) {
                    return ['found' => false];
                }

                return [
                    'found' => true,
                    'app_version' => $version->app_version,
                    'app_build' => $version->app_build,
                    'min_supported_version' => $version->min_supported_version,
                    'min_supported_build' => $version->min_supported_build,
                    'is_force_update' => $version->is_force_update,
                    'download_url' => $version->download_url,
                    'release_notes' => $version->release_notes,
                    'published_at' => $version->published_at?->toISOString(),
                ];
            },
        );

        if ($cached['found'] !== true) {
            return null;
        }

        return $cached;
    }

    /**
     * @param  array<string, string>  $context
     * @param  array<string, mixed>  $latest
     */
    private function isBelowMinimum(array $context, array $latest): bool
    {
        $minimumVersion = $latest['min_supported_version'];
        $minimumBuild = $latest['min_supported_build'];

        if ($minimumVersion === null && $minimumBuild === null) {
            return false;
        }

        if ($minimumVersion === null) {
            return $this->compareIdentifiers($context['app_build'], $minimumBuild) < 0;
        }

        return $this->isOlderThan(
            $context['app_version'],
            $context['app_build'],
            $minimumVersion,
            $minimumBuild ?? '',
        );
    }

    private function isOlderThan(
        string $currentVersion,
        string $currentBuild,
        string $targetVersion,
        string $targetBuild,
    ): bool {
        $versionComparison = $this->compareIdentifiers($currentVersion, $targetVersion);

        if ($versionComparison !== 0) {
            return $versionComparison < 0;
        }

        return $targetBuild !== ''
            && $this->compareIdentifiers($currentBuild, $targetBuild) < 0;
    }

    private function compareIdentifiers(string $left, string $right): int
    {
        if (ctype_digit($left) && ctype_digit($right)) {
            $left = ltrim($left, '0') ?: '0';
            $right = ltrim($right, '0') ?: '0';

            return strlen($left) <=> strlen($right) ?: strcmp($left, $right);
        }

        return version_compare($left, $right);
    }

    private function cacheKey(string $clientType, string $platform, string $channel): string
    {
        return 'client-version:latest:'.hash('sha256', implode('|', [
            $clientType,
            $platform,
            $channel,
        ]));
    }
}
