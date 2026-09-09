<?php

namespace Tests\Feature;

use App\ContentAssistant\Jobs\ProcessAssistantMessageJob;
use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentTargetType;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContentAssistantAsyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_send_message_dispatches_job_when_async_enabled(): void
    {
        Queue::fake();
        config(['content-assistant.async' => true]);

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation($user, ContentTargetType::Page, $page->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $user,
            'Rewrite the hero subheadline in a warmer tone.',
            $orchestrator->generateIdempotencyKey(),
        );

        $this->assertNull($proposal);
        Queue::assertPushed(ProcessAssistantMessageJob::class);
    }
}
