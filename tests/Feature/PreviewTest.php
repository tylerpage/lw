<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Page;
use App\Support\PreviewUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
