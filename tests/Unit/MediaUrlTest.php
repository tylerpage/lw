<?php

namespace Tests\Unit;

use App\Support\MediaUrl;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    public function test_it_returns_full_urls_unchanged(): void
    {
        $url = 'https://static.wikia.nocookie.net/example/image.jpeg';

        $this->assertSame($url, MediaUrl::url($url));
        $this->assertTrue(MediaUrl::isRemote($url));
    }

    public function test_it_resolves_local_paths_with_asset(): void
    {
        $this->assertSame(asset('images/photo.jpg'), MediaUrl::url('images/photo.jpg'));
        $this->assertFalse(MediaUrl::isRemote('images/photo.jpg'));
    }

    public function test_it_returns_null_for_blank_values(): void
    {
        $this->assertNull(MediaUrl::url(null));
        $this->assertNull(MediaUrl::url(''));
        $this->assertNull(MediaUrl::url('   '));
    }
}
