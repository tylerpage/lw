<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Support\ContentTargetResolver;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ContentWorkshopContextBuilder
{
    public function __construct(
        private ContentContextBuilder $contextBuilder,
        private BlockCatalogExporter $blockCatalogExporter,
    ) {}

    public function build(Model $target): string
    {
        $targetType = ContentTargetResolver::fromModel($target);
        $context = $this->contextBuilder->build($target);
        $sources = $this->contextBuilder->approvedSourcesFor($targetType);
        $instructions = $this->contextBuilder->assistantInstructions();
        $blocksField = $target instanceof Post ? 'body' : 'blocks';
        $currentBlocks = $target instanceof Post ? ($target->body ?? []) : ($target->blocks ?? []);

        $lines = [
            '# Lindsey Wegmann CMS — AI Content Workshop Brief',
            '',
            'You are helping draft structured content for the Lindsey Wegmann portfolio CMS.',
            'Return content using the **Import Content JSON schema** at the end of this brief.',
            '',
            '## Voice and claim rules',
            '',
            $instructions ?: 'Write in a clear, professional, approachable voice aligned with a marketing-engineering leader portfolio.',
            '',
            '- Do not invent client names, testimonials, awards, certifications, or performance metrics.',
            '- Use `[RESULT METRIC NEEDED]` when a metric is implied but not verified.',
            '- Do not propose new block types or layout/code changes.',
            '- Image fields should use path placeholders like `images/placeholder.jpg`.',
            '',
            '## Approved sources',
            '',
        ];

        if ($sources === []) {
            $lines[] = '_No approved sources configured._';
        } else {
            foreach ($sources as $source) {
                $lines[] = "- **{$source['title']}** ({$source['type']}): {$source['excerpt']}";
            }
        }

        $lines[] = '';
        $lines[] = '## Block catalog';
        $lines[] = '';
        $lines[] = 'Blocks are stored as flat JSON objects with a `type` field. Use only these types:';
        $lines[] = '';

        foreach ($this->blockCatalogExporter->export() as $block) {
            $lines[] = "### {$block['label']} (`{$block['type']}`)";
            $lines[] = '';
            $lines[] = 'Fields: `'.implode('`, `', $block['fields']).'`';

            if ($block['notes']) {
                $lines[] = '';
                $lines[] = "_Note: {$block['notes']}_";
            }

            $lines[] = '';
            $lines[] = 'Example:';
            $lines[] = '```json';
            $lines[] = json_encode($block['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $lines[] = '```';
            $lines[] = '';
        }

        $lines[] = '## Current record';
        $lines[] = '';
        $lines[] = '- **Target:** '.ContentTargetResolver::label($target);
        $lines[] = '- **Type:** '.$targetType->value;
        $lines[] = '- **Status:** '.$context['status'];

        if ($context['public_url']) {
            $lines[] = '- **Public URL:** '.$context['public_url'];
        }

        $lines[] = '';
        $lines[] = '### General fields';
        $lines[] = '```json';
        $lines[] = json_encode($this->generalSnapshot($target), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = "### Current {$blocksField}";
        $lines[] = '```json';
        $lines[] = json_encode($currentBlocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = '## Import Content output contract (version 1)';
        $lines[] = '';
        $lines[] = 'When the user asks you to export/import content, respond with **only** a JSON object matching this schema:';
        $lines[] = '';
        $lines[] = '```json';
        $lines[] = json_encode([
            'import_version' => 1,
            'content_type' => $targetType->value,
            'summary' => 'One-line description of the proposed content',
            'general' => [
                'title' => 'Optional title update',
                'seo_title' => 'Optional',
                'seo_description' => 'Optional',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'enabled' => true,
                    'headline' => 'Example headline',
                ],
            ],
            'warnings' => [],
            'unverified_claims' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $lines[] = '```';
        $lines[] = '';
        $lines[] = 'Rules:';
        $lines[] = '- `content_type` must be `'.$targetType->value.'` for this record.';
        $lines[] = '- `blocks` is required and must use flat block objects (not Filament `{type,data}` shape).';
        $lines[] = '- Posts use the same block array in `blocks`; the CMS maps it to the `body` column.';
        $lines[] = '- `general` is optional; include only fields you intend to change.';
        $lines[] = '- List any uncertain claims in `unverified_claims`.';

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    private function generalSnapshot(Page|Post|Project $target): array
    {
        return match ($target::class) {
            Page::class => [
                'title' => $target->title,
                'nav_label' => $target->nav_label,
                'slug' => $target->slug,
                'template' => $target->template,
                'seo_title' => $target->seo_title,
                'seo_description' => $target->seo_description,
            ],
            Post::class => [
                'title' => $target->title,
                'slug' => $target->slug,
                'excerpt' => $target->excerpt,
                'seo_title' => $target->seo_title,
                'seo_description' => $target->seo_description,
            ],
            Project::class => [
                'title' => $target->title,
                'slug' => $target->slug,
                'card_summary' => $target->card_summary,
                'seo_title' => $target->seo_title,
                'seo_description' => $target->seo_description,
            ],
        };
    }
}
