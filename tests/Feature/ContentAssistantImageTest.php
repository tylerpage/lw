<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\AssistantImageStorage;
use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentTargetType;
use App\Enums\UserRole;
use App\Models\AiMessage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentAssistantImageTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $this->editor = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->editor->assignRole(UserRole::SuperAdmin->value);
    }

    public function test_user_message_can_include_uploaded_image_urls(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);
        $imageStorage = app(AssistantImageStorage::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $stored = $imageStorage->storeMany([
            UploadedFile::fake()->image('hero-reference.jpg', 800, 600),
        ], $conversation->id);

        $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Use this image for the homepage hero and rewrite the subheadline.',
            null,
            $stored,
        );

        $message = AiMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->firstOrFail();

        $this->assertCount(1, $message->attachments());
        $this->assertStringContainsString('/storage/content-assistant/', $message->attachments()[0]['url']);
        $this->assertSame('storage/'.$message->attachments()[0]['path'], $message->attachments()[0]['public_path']);
        Storage::disk('public')->assertExists($message->attachments()[0]['path']);
    }

    public function test_hero_proposal_can_use_attached_image_public_path(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $orchestrator = app(ContentAssistantOrchestrator::class);
        $imageStorage = app(AssistantImageStorage::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $stored = $imageStorage->storeMany([
            UploadedFile::fake()->image('hero-reference.jpg'),
        ], $conversation->id);

        $proposal = $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Use this image for the homepage hero and keep both CTAs.',
            null,
            $stored,
        );

        $operation = $proposal->operations->first()?->operation;

        $this->assertSame('replace_block_fields', $operation['op'] ?? null);
        $this->assertSame($stored[0]['public_path'], $operation['fields']['image'] ?? null);
    }
}
