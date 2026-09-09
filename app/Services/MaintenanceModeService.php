<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class MaintenanceModeService
{
    public function isEnabled(): bool
    {
        return (bool) SiteSetting::get('maintenance_mode', false);
    }

    /**
     * @return array<int, string>
     */
    public function allowedIps(): array
    {
        $fromSettings = SiteSetting::get('maintenance_allowlist_ips', []);

        if (! is_array($fromSettings)) {
            $fromSettings = [];
        }

        return array_values(array_unique(array_merge(
            $this->normalizeIpList($fromSettings),
            $this->normalizeIpList(config('maintenance.allowlist_ips', [])),
        )));
    }

    public function isAllowedIp(?string $ip): bool
    {
        if ($ip === null || $ip === '') {
            return false;
        }

        return in_array($ip, $this->allowedIps(), true);
    }

    public function shouldShowMaintenance(Request $request): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        return ! $this->isAllowedIp($request->ip());
    }

    /**
     * @param  array<int, mixed>|string|null  $ips
     * @return array<int, string>
     */
    public function normalizeIpList(array|string|null $ips): array
    {
        if (is_string($ips)) {
            $ips = preg_split('/\r\n|\r|\n|,/', $ips) ?: [];
        }

        if (! is_array($ips)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $ip): string => trim((string) $ip),
            $ips,
        )));
    }
}
