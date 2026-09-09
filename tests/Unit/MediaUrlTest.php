<?php

namespace Tests\Unit;

use App\Models\MediaAsset;
use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_it_resolves_storage_paths_from_object_storage(): void
    {
        config([
            'filesystems.disks.media' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'auto',
                'bucket' => 'media-bucket',
                'url' => 'https://cdn.example.test',
                'endpoint' => 'https://example.r2.cloudflarestorage.com',
                'use_path_style_endpoint' => false,
                'throw' => false,
            ],
            'content-assistant.media_library.disk' => 'media',
            'content-assistant.media_library.directory' => 'media-library',
        ]);

        Storage::fake('media');
        Storage::disk('media')->put('media-library/hero.jpg', 'image-bytes');

        $url = MediaUrl::url('storage/media-library/hero.jpg');

        $this->assertStringContainsString('media-library/hero.jpg', $url);
        $this->assertTrue(MediaUrl::isRemote('storage/media-library/hero.jpg'));
    }

    public function test_it_uses_configured_cloud_disk_even_when_media_asset_still_says_public(): void
    {
        config([
            'filesystems.disks.lnw' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'auto',
                'bucket' => 'media-bucket',
                'url' => 'https://fls-a2b54bdf-90bc-4cd6-bd28-1cb381a308a5.laravel.cloud',
                'endpoint' => 'https://example.r2.cloudflarestorage.com',
                'use_path_style_endpoint' => false,
                'throw' => false,
            ],
            'content-assistant.media_library.disk' => 'lnw',
            'content-assistant.media_library.directory' => 'media-library',
        ]);

        $user = User::factory()->create();

        MediaAsset::withoutEvents(fn () => MediaAsset::query()->create([
            'user_id' => $user->id,
            'path' => 'media-library/hero.jpg',
            'disk' => 'public',
            'original_filename' => 'hero.jpg',
            'mime_type' => 'image/jpeg',
        ]));

        $url = MediaUrl::url('storage/media-library/hero.jpg');

        $this->assertSame(Storage::disk('lnw')->url('media-library/hero.jpg'), $url);
        $this->assertStringContainsString('fls-a2b54bdf-90bc-4cd6-bd28-1cb381a308a5.laravel.cloud', $url);
    }

    public function test_it_rewrites_app_storage_urls_to_object_storage(): void
    {
        config([
            'app.url' => 'https://lindseywegmann.com',
            'filesystems.disks.media' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'auto',
                'bucket' => 'media-bucket',
                'url' => 'https://cdn.example.test',
                'endpoint' => 'https://example.r2.cloudflarestorage.com',
                'use_path_style_endpoint' => false,
                'throw' => false,
            ],
            'content-assistant.media_library.disk' => 'media',
            'content-assistant.media_library.directory' => 'media-library',
        ]);

        Storage::fake('media');
        Storage::disk('media')->put('media-library/hero.jpg', 'image-bytes');

        $url = MediaUrl::url('https://lindseywegmann.com/storage/media-library/hero.jpg');

        $this->assertStringContainsString('media-library/hero.jpg', $url);
        $this->assertStringNotContainsString('lindseywegmann.com/storage/', $url);
    }

    public function test_it_returns_null_for_blank_values(): void
    {
        $this->assertNull(MediaUrl::url(null));
        $this->assertNull(MediaUrl::url(''));
        $this->assertNull(MediaUrl::url('   '));
    }
}
