<?php

namespace App\ContentAssistant\DTO;

readonly class ContentProposalData
{
    /**
     * @param  array<int, array<string, mixed>>  $operations
     * @param  array<int, array<string, mixed>>  $sources
     * @param  array<int, string>  $warnings
     * @param  array<int, string>  $unverifiedClaims
     */
    public function __construct(
        public string $summary,
        public array $operations,
        public array $sources = [],
        public array $warnings = [],
        public array $unverifiedClaims = [],
        public ?string $assistantMessage = null,
    ) {}

    public function toPayload(int $targetId, int $expectedRevision, string $targetType): array
    {
        return [
            'target' => [
                'type' => $targetType,
                'id' => $targetId,
                'expected_revision' => $expectedRevision,
            ],
            'summary' => $this->summary,
            'operations' => $this->operations,
            'sources' => $this->sources,
            'warnings' => $this->warnings,
            'unverified_claims' => $this->unverifiedClaims,
        ];
    }
}
