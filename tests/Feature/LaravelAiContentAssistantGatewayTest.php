<?php

namespace Tests\Feature;

use App\ContentAssistant\Agents\ContentProposalAgent;
use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\ContentAssistant\Services\LaravelAiContentAssistantGateway;
use App\Enums\ContentTargetType;
use App\Models\AiConversation;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaravelAiContentAssistantGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_maps_structured_ai_response_to_proposal_data(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $conversation = AiConversation::query()->create([
            'user_id' => $user->id,
            'title' => 'Home',
            'target_type' => ContentTargetType::Page,
            'target_id' => $page->id,
            'last_activity_at' => now(),
        ]);

        ContentProposalAgent::fake([[
            'summary' => 'Update hero subheadline',
            'assistant_message' => 'I drafted a less formal hero subheadline.',
            'operations' => [[
                'op' => 'replace_block_fields',
                'block_index' => 0,
                'fields' => ['subheadline' => 'Strategy that ships.'],
                'preserve' => ['primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url'],
            ]],
            'warnings' => [],
            'unverified_claims' => [],
            'sources' => [],
        ]]);

        config(['content-assistant.driver' => 'ai']);

        $request = new ContentAssistantRequest(
            conversation: $conversation,
            target: $page,
            targetType: ContentTargetType::Page,
            messages: [[
                'role' => 'user',
                'content' => 'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
            ]],
            latestUserMessage: 'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
            context: [
                'title' => $page->title,
                'blocks' => $page->blocks,
            ],
            approvedSources: [],
            assistantInstructions: 'Write plainly.',
            latestAttachments: [],
        );

        $proposal = app(LaravelAiContentAssistantGateway::class)->propose($request);

        $this->assertSame('Update hero subheadline', $proposal->summary);
        $this->assertSame('replace_block_fields', $proposal->operations[0]['op']);
        $this->assertSame('Strategy that ships.', $proposal->operations[0]['fields']['subheadline']);
    }
}
