<?php

namespace Tests\Feature;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\ContentAssistant\Services\MediaLibraryService;
use App\Enums\ContentTargetType;
use App\Enums\UserRole;
use App\Models\AiMessage;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
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

    public function test_media_asset_exposes_assistant_attachment_metadata(): void
    {
        $path = UploadedFile::fake()->image('hero.jpg')->store('media-library', 'public');

        $asset = MediaAsset::query()->create([
            'user_id' => $this->editor->id,
            'path' => $path,
            'disk' => 'public',
            'original_filename' => 'hero.jpg',
            'alt_text' => 'Hero reference',
        ]);

        $attachment = $asset->toAssistantAttachment();

        $this->assertSame('storage/'.$path, $attachment['public_path']);
        $this->assertSame('Hero reference', $attachment['original_name']);
        $this->assertSame($asset->id, $attachment['media_asset_id']);
    }

    public function test_assistant_message_can_use_media_library_asset(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $path = UploadedFile::fake()->image('library-hero.jpg')->store('media-library', 'public');

        $asset = MediaAsset::query()->create([
            'user_id' => $this->editor->id,
            'path' => $path,
            'disk' => 'public',
            'original_filename' => 'library-hero.jpg',
        ]);

        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Use this image for the homepage hero and keep both CTAs.',
            null,
            app(MediaLibraryService::class)->attachmentsForIds([$asset->id]),
        );

        $message = AiMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->firstOrFail();

        $this->assertSame('storage/'.$path, $message->attachments()[0]['public_path']);
    }

    public function test_media_library_is_included_in_assistant_context(): void
    {
        $path = UploadedFile::fake()->image('library.jpg')->store('media-library', 'public');

        MediaAsset::query()->create([
            'user_id' => $this->editor->id,
            'path' => $path,
            'disk' => 'public',
            'original_filename' => 'library.jpg',
            'title' => 'Library Image',
        ]);

        $assets = app(MediaLibraryService::class)->assetsForContext();

        $this->assertCount(1, $assets);
        $this->assertSame('Library Image', $assets[0]['title']);
        $this->assertSame('storage/'.$path, $assets[0]['public_path']);
    }

    public function test_pdf_can_be_stored_in_media_library(): void
    {
        $path = UploadedFile::fake()->create('migration-checklist.pdf', 120, 'application/pdf')
            ->store('media-library', 'public');

        $asset = MediaAsset::query()->create([
            'user_id' => $this->editor->id,
            'path' => $path,
            'disk' => 'public',
            'original_filename' => 'migration-checklist.pdf',
            'mime_type' => 'application/pdf',
            'title' => 'Migration Checklist',
        ]);

        $this->assertFalse($asset->isImage());
        $this->assertSame('PDF', $asset->fileTypeLabel());
        $this->assertSame('reference', $asset->toAssistantAttachment()['kind']);
    }

    public function test_pdf_library_asset_is_included_in_assistant_message_metadata(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $path = UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf')
            ->store('media-library', 'public');

        $asset = MediaAsset::query()->create([
            'user_id' => $this->editor->id,
            'path' => $path,
            'disk' => 'public',
            'original_filename' => 'notes.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $orchestrator = app(ContentAssistantOrchestrator::class);

        $conversation = $orchestrator->startConversation(
            $this->editor,
            ContentTargetType::Page,
            $page->id,
        );

        $orchestrator->sendMessage(
            $conversation,
            $this->editor,
            'Use this PDF as reference for the hero copy.',
            null,
            app(MediaLibraryService::class)->attachmentsForIds([$asset->id]),
        );

        $attachment = AiMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->firstOrFail()
            ->attachments()[0];

        $this->assertSame('application/pdf', $attachment['mime_type']);
        $this->assertSame('reference', $attachment['kind']);
    }
}
