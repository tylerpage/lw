<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentTargetType;
use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Support\PreviewUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_preview_url_renders_draft_page(): void
    {
        $this->seed();

        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $page->update(['status' => PublishStatus::Draft, 'published_at' => null]);

        $url = PreviewUrl::for($page);

        $this->get($url)->assertOk()->assertSee('Preview mode');
    }

    public function test_unsigned_preview_url_is_forbidden(): void
    {
        $this->seed();

        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $this->get(route('preview', ['type' => 'page', 'id' => $page->id]))->assertForbidden();
    }

    public function test_robots_txt_is_accessible(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');
    }

    public function test_signed_preview_renders_draft_post_without_error(): void
    {
        $this->seed();

        $post = Post::query()->create([
            'title' => 'Draft preview post',
            'slug' => 'draft-preview-post',
            'excerpt' => 'Preview excerpt',
            'body' => [[
                'type' => 'rich_text',
                'enabled' => true,
                'content' => 'Draft body copy',
            ]],
            'status' => PublishStatus::Draft,
        ]);

        $this->get(PreviewUrl::for($post))
            ->assertOk()
            ->assertSee('Draft preview post', false)
            ->assertSee('Preview mode');
    }

    public function test_signed_proposal_preview_renders_unsaved_ai_changes(): void
    {
        $this->seed();

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $editor->assignRole(UserRole::SuperAdmin->value);

        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $editor,
            ContentTargetType::Page,
            $page->id,
        );

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $editor,
            'Make the homepage hero subheadline less formal and keep both CTAs.',
        );

        $orchestrator->validateProposal($proposal, $editor);

        $url = PreviewUrl::forProposal($proposal->fresh());

        $this->get($url)
            ->assertOk()
            ->assertSee('Preview mode')
            ->assertDontSee('[DRAFT] I connect business goals, marketing, operations, and engineering', false);
    }
}
