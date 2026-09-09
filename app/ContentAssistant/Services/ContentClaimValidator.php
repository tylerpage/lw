<?php

namespace App\ContentAssistant\Services;

class ContentClaimValidator
{
    /** @var array<int, string> */
    private const FABRICATION_PATTERNS = [
        '/\b\d+(?:\.\d+)?%\s+(?:increase|growth|lift|uplift|improvement)\b/i',
        '/\b(?:ROAS|CTR|CVR|conversion rate|revenue|sales)\b.*\b\d+/i',
        '/\$\d+/',
        '/\b(?:increased|grew|boosted|generated)\b.*\bby\b.*\b\d+/i',
        '/\b(?:award|certified|certification)\b/i',
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    public function validate(array $payload): array
    {
        $claims = [];
        $placeholders = config('content-assistant.internal_placeholders', []);

        foreach ($this->extractStrings($payload) as $string) {
            foreach ($placeholders as $placeholder) {
                if (str_contains($string, $placeholder)) {
                    $claims[] = "Internal placeholder found: {$placeholder}";
                }
            }

            foreach (self::FABRICATION_PATTERNS as $pattern) {
                if (preg_match($pattern, $string)) {
                    $claims[] = 'Potential unverified metric or claim: '.str($string)->limit(120)->toString();
                    break;
                }
            }
        }

        return array_values(array_unique($claims));
    }

    /**
     * @return array<int, string>
     */
    private function extractStrings(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        $strings = [];
        foreach ($value as $item) {
            $strings = array_merge($strings, $this->extractStrings($item));
        }

        return $strings;
    }
}
