<?php

namespace Tests\Feature;

use App\ContentAssistant\Enums\ContentImportMode;
use App\ContentAssistant\Services\BlockCatalogExporter;
use App\ContentAssistant\Services\ContentImportService;
use App\ContentAssistant\Services\ContentWorkshopContextBuilder;
use App\Enums\PublishStatus;
use App\Enums\UserRole;
use App\Models\ContentAssistantAuditEvent;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentImportTest extends TestCase
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

    public function test_context_builder_includes_all_block_types_and_voice_rules(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $markdown = app(ContentWorkshopContextBuilder::class)->build($page);

        $this->assertStringContainsString('Voice and claim rules', $markdown);
        $this->assertStringContainsString('Import Content output contract', $markdown);

        foreach (app(BlockCatalogExporter::class)->export() as $block) {
            $this->assertStringContainsString('`'.$block['type'].'`', $markdown);
        }

        $this->assertCount(18, app(BlockCatalogExporter::class)->export());
    }

    public function test_valid_import_applies_hero_rewrite_as_draft(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $originalStatus = $page->status;

        $payload = json_encode([
            'import_version' => 1,
            'content_type' => 'page',
            'summary' => 'Refresh homepage hero',
            'blocks' => [
                [
                    'type' => 'hero',
                    'enabled' => true,
                    'headline' => 'Imported headline',
                    'subheadline' => 'Imported subheadline',
                    'primary_cta_label' => 'Work',
                    'primary_cta_url' => '/work',
                ],
            ],
        ]);

        $service = app(ContentImportService::class);
        $validation = $service->validatePayload($page, $payload, ContentImportMode::Replace);

        $this->assertSame([], $validation['errors']);
        $this->assertNotNull($validation['data']);

        $service->apply($page, $validation['data'], $this->editor, ContentImportMode::Replace);

        $page->refresh();

        $this->assertSame('Imported headline', $page->blocks[0]['headline'] ?? null);
        $this->assertTrue(
            ContentAssistantAuditEvent::query()
                ->where('event_type', 'content_import_applied')
                ->exists()
        );

        if ($originalStatus === PublishStatus::Published) {
            $this->assertSame(PublishStatus::Published, $page->status);
            $this->assertTrue($page->has_unpublished_changes);
            $this->assertNotNull($page->published_revision_id);
        } else {
            $this->assertSame(PublishStatus::Draft, $page->status);
        }
    }

    public function test_invalid_import_is_rejected_with_errors(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $payload = json_encode([
            'import_version' => 1,
            'content_type' => 'page',
            'summary' => 'Bad import',
            'blocks' => [
                ['type' => 'unknown_block', 'enabled' => true],
            ],
        ]);

        $result = app(ContentImportService::class)->validatePayload(
            $page,
            $payload,
            ContentImportMode::Replace,
        );

        $this->assertNotEmpty($result['errors']);
        $this->assertNull($result['data']);
    }

    public function test_import_with_fabricated_metric_is_flagged_in_warnings(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $payload = json_encode([
            'import_version' => 1,
            'content_type' => 'page',
            'summary' => 'Metric-heavy hero',
            'blocks' => [
                [
                    'type' => 'hero',
                    'enabled' => true,
                    'headline' => 'Increased revenue by 42%',
                    'subheadline' => 'Trusted results',
                ],
            ],
        ]);

        $result = app(ContentImportService::class)->validatePayload(
            $page,
            $payload,
            ContentImportMode::Replace,
        );

        $this->assertSame([], $result['errors']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_append_mode_preserves_existing_blocks(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();
        $originalCount = count($page->blocks ?? []);

        $payload = json_encode([
            'import_version' => 1,
            'content_type' => 'page',
            'summary' => 'Append quote block',
            'blocks' => [
                [
                    'type' => 'quote',
                    'enabled' => true,
                    'quote' => 'Appended quote',
                ],
            ],
        ]);

        $service = app(ContentImportService::class);
        $validation = $service->validatePayload($page, $payload, ContentImportMode::Append);

        $service->apply($page, $validation['data'], $this->editor, ContentImportMode::Append);

        $page->refresh();

        $this->assertSame($originalCount + 1, count($page->blocks));
        $this->assertSame('quote', $page->blocks[array_key_last($page->blocks)]['type'] ?? null);
    }
}
