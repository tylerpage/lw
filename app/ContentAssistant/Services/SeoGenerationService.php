<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Contracts\SeoGenerationGateway;
use App\ContentAssistant\Enums\SeoGenerationMode;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;

class SeoGenerationService
{
    public function __construct(
        private SeoGenerationGateway $gateway,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function generate(Page|Post|Project $record, SeoGenerationMode $mode): array
    {
        return $this->gateway->generate($record, $mode);
    }
}
