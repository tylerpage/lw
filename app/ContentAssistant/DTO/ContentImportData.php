<?php

namespace App\ContentAssistant\DTO;

use App\Enums\ContentTargetType;

readonly class ContentImportData
{
    /**
     * @param  array<string, mixed>  $general
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, string>  $warnings
     * @param  array<int, string>  $unverifiedClaims
     */
    public function __construct(
        public int $importVersion,
        public ContentTargetType $contentType,
        public string $summary,
        public array $general,
        public array $blocks,
        public array $warnings = [],
        public array $unverifiedClaims = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            importVersion: (int) ($payload['import_version'] ?? 0),
            contentType: ContentTargetType::from($payload['content_type']),
            summary: (string) ($payload['summary'] ?? ''),
            general: $payload['general'] ?? [],
            blocks: $payload['blocks'] ?? [],
            warnings: $payload['warnings'] ?? [],
            unverifiedClaims: $payload['unverified_claims'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'import_version' => $this->importVersion,
            'content_type' => $this->contentType->value,
            'summary' => $this->summary,
            'general' => $this->general,
            'blocks' => $this->blocks,
            'warnings' => $this->warnings,
            'unverified_claims' => $this->unverifiedClaims,
        ];
    }
}
