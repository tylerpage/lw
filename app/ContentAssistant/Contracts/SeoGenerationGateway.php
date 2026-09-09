<?php

namespace App\ContentAssistant\Contracts;

use App\ContentAssistant\Enums\SeoGenerationMode;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;

interface SeoGenerationGateway
{
    /**
     * @return array<string, string|null>
     */
    public function generate(Page|Post|Project $record, SeoGenerationMode $mode): array;
}
