<?php

namespace App\ContentAssistant\Services;

use App\PageBlocks\BlockRegistry;

class BlockCatalogExporter
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function export(): array
    {
        return collect(BlockRegistry::all())
            ->map(function (string $class, string $type): array {
                $example = $class::fromArray($this->sampleDataFor($type))->toArray();

                return [
                    'type' => $type,
                    'label' => $class::label(),
                    'fields' => array_keys(array_diff_key($example, ['type' => true])),
                    'example' => $example,
                    'notes' => $this->notesFor($type),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleDataFor(string $type): array
    {
        return match ($type) {
            'hero' => ['type' => 'hero', 'enabled' => true, 'headline' => 'Headline'],
            'rich_text' => ['type' => 'rich_text', 'enabled' => true, 'content' => 'Body copy'],
            'image_text' => ['type' => 'image_text', 'enabled' => true, 'content' => 'Text', 'image' => 'images/example.jpg', 'image_alt' => 'Alt', 'image_position' => 'left'],
            'full_width_image' => ['type' => 'full_width_image', 'enabled' => true, 'image' => 'images/example.jpg', 'image_alt' => 'Alt'],
            'cta_banner' => ['type' => 'cta_banner', 'enabled' => true, 'heading' => 'CTA', 'cta_label' => 'Go', 'cta_url' => '/contact'],
            'card_grid' => ['type' => 'card_grid', 'enabled' => true, 'cards' => [['title' => 'Card']]],
            'capabilities_grid' => ['type' => 'capabilities_grid', 'enabled' => true],
            'featured_projects' => ['type' => 'featured_projects', 'enabled' => true, 'limit' => 2],
            'featured_posts' => ['type' => 'featured_posts', 'enabled' => true, 'limit' => 2],
            'testimonials' => ['type' => 'testimonials', 'enabled' => true, 'limit' => 2],
            'stats' => ['type' => 'stats', 'enabled' => true, 'stats' => [['label' => 'Metric', 'value' => '10']]],
            'timeline' => ['type' => 'timeline', 'enabled' => true],
            'logo_strip' => ['type' => 'logo_strip', 'enabled' => true, 'logos' => [['name' => 'Logo']]],
            'faq' => ['type' => 'faq', 'enabled' => true, 'items' => [['question' => 'Q', 'answer' => 'A']]],
            'quote' => ['type' => 'quote', 'enabled' => true, 'quote' => 'Quote text'],
            'embed' => ['type' => 'embed', 'enabled' => true, 'provider' => 'youtube', 'url' => 'https://www.youtube.com/embed/test'],
            'spacer' => ['type' => 'spacer', 'enabled' => true, 'size' => 'md'],
            'personality' => ['type' => 'personality', 'enabled' => true, 'status_line' => 'In my era'],
            default => ['type' => $type, 'enabled' => true],
        };
    }

    private function notesFor(string $type): ?string
    {
        return match ($type) {
            'capabilities_grid' => 'Heading only; capability items come from the database.',
            'timeline' => 'Heading only; timeline entries come from the database.',
            'featured_projects', 'featured_posts', 'testimonials' => 'Query block; set heading and limit only.',
            'rich_text' => 'Plain text content only until HTML rendering is supported.',
            default => null,
        };
    }
}
