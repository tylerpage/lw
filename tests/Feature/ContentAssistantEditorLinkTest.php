<?php

namespace Tests\Feature;

use App\Enums\ContentTargetType;
use App\Filament\Pages\ContentAssistant;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAssistantEditorLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_content_assistant_accepts_return_url_for_edit_flow(): void
    {
        $post = Post::query()->firstOrFail();
        $returnUrl = PostResource::getUrl('edit', ['record' => $post]);

        $url = ContentAssistant::getUrl([
            'targetType' => ContentTargetType::Post->value,
            'targetId' => $post->id,
            'start' => true,
            'returnUrl' => $returnUrl,
        ]);

        $this->assertStringContainsString('targetType=post', $url);
        $this->assertStringContainsString('targetId='.$post->id, $url);
        $this->assertStringContainsString('returnUrl=', $url);
    }
}
