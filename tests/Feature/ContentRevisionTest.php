<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\ContentAssistant\Services\ContentRevisionService;
use App\Enums\ContentTargetType;
use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ai_draft_save_keeps_page_published_and_pins_live_revision(): void
    {
        $editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $liveSubheadline = collect($page->blocks)->firstWhere('type', 'hero')['subheadline'] ?? null;

        $orchestrator = app(ContentAssistantOrchestrator::class);
        $conversation = $orchestrator->startConversation($editor, ContentTargetType::Page, $page->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $editor,
            'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $editor);
        $orchestrator->applyDraft($proposal->fresh(), $editor);

        $page->refresh();

        $this->assertSame(PublishStatus::Published, $page->status);
        $this->assertTrue($page->has_unpublished_changes);
        $this->assertNotNull($page->published_revision_id);
        $this->assertDatabaseHas('page_revisions', [
            'page_id' => $page->id,
            'id' => $page->published_revision_id,
        ]);

        $this->get('/')->assertOk()->assertSee($liveSubheadline, false);

        $draftSubheadline = collect($page->blocks)->firstWhere('type', 'hero')['subheadline'] ?? null;
        $this->assertNotSame($liveSubheadline, $draftSubheadline);
    }

    public function test_publish_clears_unpublished_changes_flag(): void
    {
        $editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);
        $conversation = $orchestrator->startConversation($editor, ContentTargetType::Page, $page->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $editor,
            'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $editor);
        $orchestrator->approveAndPublish($proposal->fresh(), $editor);

        $page->refresh();

        $this->assertSame(PublishStatus::Published, $page->status);
        $this->assertFalse($page->has_unpublished_changes);
        $this->assertNull($page->published_revision_id);
    }

    public function test_manual_publish_makes_draft_changes_live(): void
    {
        $editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $liveSubheadline = collect($page->blocks)->firstWhere('type', 'hero')['subheadline'] ?? null;

        $orchestrator = app(ContentAssistantOrchestrator::class);
        $conversation = $orchestrator->startConversation($editor, ContentTargetType::Page, $page->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $editor,
            'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $editor);
        $orchestrator->applyDraft($proposal->fresh(), $editor);

        $page->refresh();
        $draftSubheadline = collect($page->blocks)->firstWhere('type', 'hero')['subheadline'] ?? null;

        $this->get('/')->assertOk()->assertSee($liveSubheadline, false);

        app(ContentRevisionService::class)->publishPendingChanges($page, $editor);
        $page->save();
        $page->refresh();

        $this->assertFalse($page->has_unpublished_changes);
        $this->assertNull($page->published_revision_id);
        $this->get('/')->assertOk()->assertSee($draftSubheadline, false);
    }
}
