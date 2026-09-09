<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageUrl
{
    public static function urlForPath(string $objectPath, ?string $storedDisk = null): string
    {
        $objectPath = ltrim($objectPath, '/');
        $disk = self::resolveDisk($objectPath, $storedDisk);

        if (self::isCloudDisk($disk)) {
            return Storage::disk($disk)->url($objectPath);
        }

        return asset('storage/'.$objectPath);
    }

    public static function resolveDisk(string $objectPath, ?string $storedDisk = null): string
    {
        $configuredDisk = self::diskForObjectPath($objectPath);

        if (self::isCloudDisk($configuredDisk)) {
            return $configuredDisk;
        }

        if ($storedDisk && config("filesystems.disks.{$storedDisk}")) {
            return $storedDisk;
        }

        return $configuredDisk;
    }

    public static function diskForObjectPath(string $objectPath): string
    {
        $objectPath = ltrim($objectPath, '/');
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

    public static function isCloudDisk(string $disk): bool
    {
        return config("filesystems.disks.{$disk}.driver") === 's3';
    }

    public static function objectPathFromStorageReference(string $source): string
    {
        $source = trim($source);

        if (str_contains($source, '/storage/')) {
            $source = 'storage/'.Str::after($source, '/storage/');
        }

        return ltrim(Str::after($source, 'storage/'), '/');
    }

    public static function isStorageReference(?string $source): bool
    {
        if ($source === null || trim($source) === '') {
            return false;
        }

        $source = trim($source);

        return str_starts_with($source, 'storage/')
            || str_contains($source, '/storage/');
    }

    public static function rewriteAppStorageUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_contains($path, '/storage/')) {
            return null;
        }

        return self::urlForPath(ltrim(Str::after($path, '/storage/'), '/'));
    }
}
