<?php

namespace App\ContentAssistant\Support;

use App\ContentAssistant\DTO\ContentProposalData;

class ContentProposalResponseMapper
{
    /**
     * @param  array<string, mixed>  $structured
     */
    public function toProposalData(array $structured): ContentProposalData
    {
        $operations = collect($structured['operations'] ?? [])
            ->map(fn (mixed $operation): array => $this->normalizeOperation(is_array($operation) ? $operation : []))
            ->filter(fn (array $operation): bool => ($operation['op'] ?? '') !== '')
            ->values()
            ->all();

        $warnings = $this->stringList($structured['warnings'] ?? []);
        $unverifiedClaims = $this->stringList($structured['unverified_claims'] ?? []);
        $sources = collect($structured['sources'] ?? [])
            ->filter(fn (mixed $source): bool => is_array($source))
            ->values()
            ->all();

        return new ContentProposalData(
            summary: trim((string) ($structured['summary'] ?? 'Content proposal')),
            operations: $operations,
            sources: $sources,
            warnings: $warnings,
            unverifiedClaims: $unverifiedClaims,
            assistantMessage: trim((string) ($structured['assistant_message'] ?? '')) ?: null,
        );
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function normalizeOperation(array $operation): array
    {
        $normalized = [];

        foreach ($operation as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if ($key === 'fields' && is_array($value)) {
                $fields = collect($value)
                    ->filter(fn (mixed $fieldValue): bool => $fieldValue !== null && $fieldValue !== '')
                    ->all();

                if ($fields !== []) {
                    $normalized['fields'] = $fields;
                }

                continue;
            }

            if ($key === 'preserve' && is_array($value)) {
                $preserve = $this->stringList($value);

                if ($preserve !== []) {
                    $normalized['preserve'] = $preserve;
                }

                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            $values,
        )));
    }
}
