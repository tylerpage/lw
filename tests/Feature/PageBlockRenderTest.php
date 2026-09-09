<?php

namespace Tests\Feature;

use App\PageBlocks\BlockRegistry;
use App\PageBlocks\Blocks\HeroBlock;
use App\Services\PageBlockRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBlockRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_registered_blocks_can_be_instantiated_from_array(): void
    {
        foreach (BlockRegistry::all() as $type => $class) {
            $sample = $this->sampleDataFor($type);
            $block = BlockRegistry::fromArray($sample);

            $this->assertInstanceOf($class, $block);
            $this->assertSame($type, $block::type());
            $this->assertIsArray($block->toArray());
        }
    }

    public function test_rich_text_block_renders_markdown(): void
    {
        $html = app(PageBlockRenderer::class)->render([
            [
                'type' => 'rich_text',
                'enabled' => true,
                'content' => "## Section title\n\n**Bold copy**",
            ],
        ]);

        $this->assertStringContainsString('<h2>Section title</h2>', $html);
        $this->assertStringContainsString('<strong>Bold copy</strong>', $html);
    }

    public function test_hero_block_renders_html(): void
    {
        $html = app(PageBlockRenderer::class)->render([
            HeroBlock::fromArray([
                'type' => 'hero',
                'enabled' => true,
                'headline' => 'Test headline',
                'subheadline' => 'Test subheadline',
                'primary_cta_label' => 'Learn more',
                'primary_cta_url' => '/work',
            ])->toArray(),
        ]);

        $this->assertStringContainsString('Test headline', $html);
        $this->assertStringContainsString('Learn more', $html);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sampleDataFor(string $type): array
    {
        return match ($type) {
            'hero' => ['type' => 'hero', 'enabled' => true, 'headline' => 'Headline'],
            'rich_text' => ['type' => 'rich_text', 'enabled' => true, 'content' => 'Body copy'],
            'image_text' => ['type' => 'image_text', 'enabled' => true, 'content' => 'Text', 'image' => '/img.jpg', 'image_alt' => 'Alt', 'image_position' => 'left'],
            'full_width_image' => ['type' => 'full_width_image', 'enabled' => true, 'image' => '/img.jpg', 'image_alt' => 'Alt'],
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
}
