<?php

namespace App\Services;

class AnalyticsService
{
    public function isEnabled(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return (bool) config('analytics.debug', false);
        }

        return filled(config('analytics.plausible_domain')) || filled(config('analytics.gtm_id'));
    }

    public function provider(): string
    {
        if (filled(config('analytics.plausible_domain'))) {
            return 'plausible';
        }

        if (filled(config('analytics.gtm_id'))) {
            return 'gtm';
        }

        return 'none';
    }

    /**
     * @return array<string, mixed>
     */
    public function eventPayload(string $event, array $properties = []): array
    {
        $payload = [
            'event' => $event,
            'properties' => $this->sanitizeProperties($properties),
        ];

        if ($this->debugMode()) {
            $payload['debug'] = true;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    public function sanitizeProperties(array $properties): array
    {
        $forbidden = ['email', 'name', 'message', 'phone', 'organization'];

        return collect($properties)
            ->reject(fn ($value, $key) => in_array(strtolower((string) $key), $forbidden, true))
            ->all();
    }

    public function debugMode(): bool
    {
        return app()->environment(['local', 'staging']) || config('analytics.debug', false);
    }

    public function scriptConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'provider' => $this->provider(),
            'plausible_domain' => config('analytics.plausible_domain'),
            'gtm_id' => config('analytics.gtm_id'),
            'debug' => $this->debugMode(),
        ];
    }
}
