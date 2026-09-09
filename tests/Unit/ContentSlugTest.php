<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Support\ContentSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_it_generates_slug_from_title(): void
    {
        $this->assertSame('marketing-and-operations', ContentSlug::fromTitle('Marketing and Operations'));
    }

    public function test_it_ensures_unique_slug_when_blank(): void
    {
        Page::query()->where('slug', 'home')->firstOrFail();

        $data = ContentSlug::ensure(
            ['title' => 'Home', 'slug' => ''],
            Page::class,
        );

        $this->assertSame('home-2', $data['slug']);
    }

    public function test_it_preserves_provided_slug(): void
    {
        $data = ContentSlug::ensure(
            ['title' => 'Custom Title', 'slug' => 'custom-slug'],
            Page::class,
        );

        $this->assertSame('custom-slug', $data['slug']);
    }
}
