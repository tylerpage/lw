<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->publishCorePages();
    }

    protected function publishCorePages(): void
    {
        Page::query()->whereIn('slug', ['home', 'about', 'contact', 'privacy'])->update([
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_home_page_is_accessible(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_about_page_is_accessible(): void
    {
        $this->get('/about')->assertOk();
    }

    public function test_work_index_is_accessible(): void
    {
        $this->get('/work')->assertOk();
    }

    public function test_insights_index_is_accessible(): void
    {
        $this->get('/insights')->assertOk();
    }

    public function test_contact_page_is_accessible(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_sitemap_is_accessible(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml');
    }

    public function test_llms_txt_is_accessible(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('# Lindsey Wegmann', false)
            ->assertSee(url('/sitemap.xml'), false);
    }

    public function test_rss_feed_is_accessible(): void
    {
        $this->get('/feed.xml')->assertOk();
    }

    public function test_search_page_is_accessible(): void
    {
        $this->get('/search')->assertOk();
    }
}
