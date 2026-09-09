<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentProposalStatus;
use App\Enums\ContentTargetType;
use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\ContentAssistantAuditEvent;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $this->editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->editor->assignRole(UserRole::SuperAdmin->value);
    }

    public function test_editor_can_generate_homepage_hero_proposal_without_publishing(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $originalBlocks = $page->blocks;
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Make the homepage hero focus more on connecting marketing and engineering, but keep both CTAs.',
        );

        $validation = $orchestrator->validateProposal($proposal, $this->editor);

        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['diff']);
        $this->assertSame(ContentProposalStatus::Validated, $proposal->fresh()->status);

        $page->refresh();
        $this->assertSame(PublishStatus::Published, $page->status);
        $this->assertSame($originalBlocks, $page->blocks);
    }

    public function test_validated_proposal_can_be_saved_as_draft(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Rewrite the homepage hero subheadline in a less formal tone and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $this->editor);
        $orchestrator->applyDraft($proposal->fresh(), $this->editor);

        $page->refresh();

        $this->assertSame(PublishStatus::Draft, $page->status);
        $this->assertNotSame(
            '[DRAFT] I connect business goals, marketing, operations, and engineering—turning complex commerce challenges into clear, executable plans.',
            collect($page->blocks)->firstWhere('type', 'hero')['subheadline'] ?? null,
        );
        $this->assertDatabaseHas('page_revisions', ['page_id' => $page->id]);
    }

    public function test_unsupported_code_request_produces_no_operations(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Change the Tailwind CSS layout of the homepage header.',
        );

        $this->assertSame([], $proposal->payload['operations']);
        $this->assertNotEmpty($proposal->warnings);
    }

    public function test_audit_events_are_recorded_for_generation_and_draft_save(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Make the homepage hero subheadline less formal and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $this->editor);
        $orchestrator->applyDraft($proposal->fresh(), $this->editor);

        $this->assertTrue(
            ContentAssistantAuditEvent::query()->where('event_type', 'assistant_proposal_generated')->exists()
        );
        $this->assertTrue(
            ContentAssistantAuditEvent::query()->where('event_type', 'assistant_draft_saved')->exists()
        );
    }

    public function test_content_propose_command_outputs_structured_json(): void
    {
        $this->artisan('content:propose', [
            'type' => 'page',
            'slug' => 'home',
            '--message' => 'Improve SEO title and meta description.',
            '--user' => $this->editor->id,
        ])->assertSuccessful();
    }
}
