<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\ContentWorkshopContextBuilder;
use App\Enums\ContentTargetType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWorkshopDraftContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_post_context_can_be_exported_from_form_data(): void
    {
        $markdown = app(ContentWorkshopContextBuilder::class)->buildForDraft(ContentTargetType::Post, [
            'title' => 'New thought leadership post',
            'slug' => 'new-thought-leadership-post',
            'excerpt' => 'A working draft excerpt.',
            'body' => [[
                'type' => 'rich_text',
                'enabled' => true,
                'content' => 'Draft body copy.',
            ]],
        ]);

        $this->assertStringContainsString('AI Content Workshop Brief', $markdown);
        $this->assertStringContainsString('content_type', $markdown);
        $this->assertStringContainsString('New thought leadership post', $markdown);
        $this->assertStringContainsString('Draft body copy.', $markdown);
    }
}
