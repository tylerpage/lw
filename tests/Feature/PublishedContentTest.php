<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_draft_home_page_returns_not_found(): void
    {
        Page::query()->where('slug', 'home')->update([
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ]);

        $this->get('/')->assertNotFound();
    }

    public function test_published_home_page_is_visible(): void
    {
        Page::query()->where('slug', 'home')->update([
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/')->assertOk();
    }

    public function test_draft_project_is_not_public(): void
    {
        $project = Project::query()->firstOrFail();
        $project->update([
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ]);

        $this->get('/work/'.$project->slug)->assertNotFound();
    }

    public function test_published_project_is_visible(): void
    {
        $project = Project::query()->firstOrFail();
        $project->update([
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/work/'.$project->slug)->assertOk();
    }

    public function test_draft_post_is_not_in_sitemap(): void
    {
        $post = Post::query()->firstOrFail();
        $post->update([
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ]);

        $this->get('/sitemap.xml')->assertDontSee('/insights/'.$post->slug);
    }

    public function test_published_post_appears_in_sitemap(): void
    {
        $post = Post::query()->firstOrFail();
        $post->update([
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/sitemap.xml')->assertSee('/insights/'.$post->slug);
    }

    public function test_draft_post_is_not_in_llms_txt(): void
    {
        $post = Post::query()->firstOrFail();
        $post->update([
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ]);

        $this->get('/llms.txt')->assertDontSee('/insights/'.$post->slug);
    }

    public function test_published_post_appears_in_llms_txt(): void
    {
        $post = Post::query()->firstOrFail();
        $post->update([
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/llms.txt')->assertSee('/insights/'.$post->slug);
    }
}
