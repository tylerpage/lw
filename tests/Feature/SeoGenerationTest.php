<?php

namespace Tests\Feature;

use App\ContentAssistant\Enums\SeoGenerationMode;
use App\ContentAssistant\Services\SeoGenerationService;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_page_seo_is_generated_from_block_content(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $result = app(SeoGenerationService::class)->generate($page, SeoGenerationMode::FromContent);

        $this->assertNotEmpty($result['seo_title']);
        $this->assertNotEmpty($result['seo_description']);
        $this->assertSame($result['seo_title'], $result['og_title']);
        $this->assertStringNotContainsString('[DRAFT]', $result['seo_title']);
    }

    public function test_post_meta_only_generation_uses_excerpt(): void
    {
        $post = Post::query()->firstOrFail();
        $post->excerpt = 'A concise summary for search results.';
        $post->save();

        $result = app(SeoGenerationService::class)->generate($post, SeoGenerationMode::MetaOnly);

        $this->assertArrayHasKey('seo_title', $result);
        $this->assertArrayHasKey('seo_description', $result);
        $this->assertStringContainsString('concise summary', $result['seo_description']);
        $this->assertArrayNotHasKey('og_title', $result);
    }

    public function test_open_graph_generation_falls_back_to_existing_meta(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $page->seo_title = 'Custom SEO title';
        $page->seo_description = 'Custom SEO description';
        $page->save();

        $result = app(SeoGenerationService::class)->generate($page, SeoGenerationMode::OpenGraphOnly);

        $this->assertSame('Custom SEO title', $result['og_title']);
        $this->assertSame('Custom SEO description', $result['og_description']);
    }
}
