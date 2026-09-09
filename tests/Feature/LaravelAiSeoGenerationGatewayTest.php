<?php

namespace Tests\Feature;

use App\ContentAssistant\Agents\SeoProposalAgent;
use App\ContentAssistant\Enums\SeoGenerationMode;
use App\ContentAssistant\Services\SeoGenerationService;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaravelAiSeoGenerationGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ai_seo_gateway_maps_structured_response(): void
    {
        SeoProposalAgent::fake([[
            'seo_title' => 'Marketing Engineering Insights | Lindsey Wegmann',
            'seo_description' => 'Explore how marketing and engineering teams align to ship better outcomes.',
            'og_title' => 'Marketing Engineering Insights',
            'og_description' => 'Explore how marketing and engineering teams align to ship better outcomes.',
            'canonical_url' => 'https://example.com/insights/test-post',
            'notes' => '',
        ]]);

        config(['content-assistant.driver' => 'ai']);

        $post = Post::query()->firstOrFail();
        $post->title = 'Marketing Engineering Insights';
        $post->excerpt = 'Alignment strategies for cross-functional teams.';
        $post->save();

        $result = app(SeoGenerationService::class)->generate($post, SeoGenerationMode::FromContent);

        $this->assertSame('Marketing Engineering Insights | Lindsey Wegmann', $result['seo_title']);
        $this->assertStringContainsString('marketing and engineering', $result['seo_description']);
        $this->assertSame('Marketing Engineering Insights', $result['og_title']);
    }
}
