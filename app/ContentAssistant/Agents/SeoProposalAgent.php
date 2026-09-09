<?php

namespace App\ContentAssistant\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class SeoProposalAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private string $systemInstructions,
    ) {}

    public function instructions(): Stringable|string
    {
        return $this->systemInstructions;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'seo_title' => $schema->string(),
            'seo_description' => $schema->string(),
            'og_title' => $schema->string(),
            'og_description' => $schema->string(),
            'canonical_url' => $schema->string(),
            'notes' => $schema->string(),
        ];
    }
}
