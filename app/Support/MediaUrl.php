<?php

namespace App\Support;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUrl
{
    public static function url(?string $source): ?string
    {
        if ($source === null) {
            return null;
        }

        $source = trim($source);

        if ($source === '') {
            return null;
        }

        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return self::rewriteAppStorageUrl($source) ?? $source;
        }

        if (str_starts_with($source, '//')) {
            return $source;
        }

        if (self::isStoragePath($source)) {
            return self::resolveStoragePath($source);
        }

        return asset(ltrim($source, '/'));
    }

    public static function isRemote(?string $source): bool
    {
        if ($source === null || trim($source) === '') {
            return false;
        }

        $source = trim($source);

        if (filter_var($source, FILTER_VALIDATE_URL) !== false || str_starts_with($source, '//')) {
            return true;
        }

        if (! self::isStoragePath($source)) {
            return false;
        }

        $objectPath = self::objectPathFromStorageReference($source);
        $disk = self::diskForObjectPath($objectPath);

        return config("filesystems.disks.{$disk}.driver") === 's3';
    }

    public static function isStoragePath(string $source): bool
    {
        $source = trim($source);

        return str_starts_with($source, 'storage/')
            || str_contains($source, '/storage/');
    }

    public static function objectPathFromStorageReference(string $source): string
    {
        $source = trim($source);

        if (str_contains($source, '/storage/')) {
            $source = 'storage/'.Str::after($source, '/storage/');
        }

        return ltrim(Str::after($source, 'storage/'), '/');
    }

    public static function diskForObjectPath(string $objectPath): string
    {
        $mediaDirectory = trim((string) config('content-assistant.media_library.directory', 'media-library'), '/');
        $attachmentDirectory = trim((string) config('content-assistant.attachments.directory', 'content-assistant'), '/');

        if ($mediaDirectory !== '' && str_starts_with($objectPath, $mediaDirectory.'/')) {
            return (string) config('content-assistant.media_library.disk', 'public');
        }

        if ($attachmentDirectory !== '' && str_starts_with($objectPath, $attachmentDirectory.'/')) {
            return (string) config('content-assistant.attachments.disk', 'public');
        }

        return 'public';
    }

    private static function rewriteAppStorageUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_contains($path, '/storage/')) {
            return null;
        }

        return self::resolveStoragePath('storage/'.ltrim(Str::after($path, '/storage/'), '/'));
    }

    private static function resolveStoragePath(string $source): string
    {
        $objectPath = self::objectPathFromStorageReference($source);

        $asset = MediaAsset::query()->where('path', $objectPath)->first();

        if ($asset) {
            return $asset->url();
        }

        $disk = self::diskForObjectPath($objectPath);

        if (config("filesystems.disks.{$disk}.driver") === 's3') {
            return Storage::disk($disk)->url($objectPath);
        }

        return asset(ltrim($source, '/'));
    }
}
