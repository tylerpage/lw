<?php

namespace App\ContentAssistant\Support;

use App\ContentAssistant\Enums\SeoGenerationMode;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;

class SeoPromptBuilder
{
    public function buildInstructions(SeoGenerationMode $mode): string
    {
        $site = (string) config('app.name');

        return implode("\n", [
            "You are an SEO copywriter for {$site}, a marketing-engineering leader portfolio site.",
            'Write search-optimized metadata that is accurate, compelling, and keyword-rich without keyword stuffing.',
            'Never invent client names, awards, metrics, or testimonials.',
            'Strip any [DRAFT] prefix from titles.',
            'Keep seo_title at or under 60 characters and seo_description at or under 160 characters.',
            'Open Graph fields may be slightly longer but should stay concise and match the page intent.',
            $this->modeInstruction($mode),
        ]);
    }

    public function buildPrompt(Page|Post|Project $record, SeoGenerationMode $mode): string
    {
        $targetType = ContentTargetResolver::fromModel($record);
        $blocksField = $record instanceof Post ? 'body' : 'blocks';
        $blocks = $record instanceof Post ? ($record->body ?? []) : ($record->blocks ?? []);

        $payload = [
            'content_type' => $targetType->value,
            'mode' => $mode->value,
            'title' => $record->title,
            'slug' => $record->slug,
            'existing_seo' => [
                'seo_title' => $record->seo_title,
                'seo_description' => $record->seo_description,
                'og_title' => $record->og_title,
                'og_description' => $record->og_description,
                'canonical_url' => $record->canonical_url,
            ],
            'summary_fields' => $this->summaryFields($record),
            $blocksField => $blocks,
            'public_url' => ContentTargetResolver::publicUrl($record),
        ];

        return "Generate SEO metadata for this {$targetType->label()}.\n\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string, mixed>
     */
    private function summaryFields(Page|Post|Project $record): array
    {
        return match ($record::class) {
            Post::class => ['excerpt' => $record->excerpt],
            Project::class => ['card_summary' => $record->card_summary],
            Page::class => ['nav_label' => $record->nav_label, 'template' => $record->template],
        };
    }

    private function modeInstruction(SeoGenerationMode $mode): string
    {
        return match ($mode) {
            SeoGenerationMode::MetaOnly => 'Return only seo_title and seo_description. Leave og_* and canonical_url empty.',
            SeoGenerationMode::OpenGraphOnly => 'Return only og_title and og_description. Prefer improving existing meta when present.',
            SeoGenerationMode::ImproveExisting => 'Improve existing SEO fields when present; otherwise derive from content.',
            SeoGenerationMode::FromContent => 'Return seo_title, seo_description, og_title, og_description, and canonical_url when a public URL is known.',
        };
    }
}
