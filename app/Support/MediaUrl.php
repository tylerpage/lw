<?php

namespace App\Support;

use App\Models\MediaAsset;

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
            return StorageUrl::rewriteAppStorageUrl($source) ?? $source;
        }

        if (str_starts_with($source, '//')) {
            return $source;
        }

        if (StorageUrl::isStorageReference($source)) {
            return StorageUrl::urlForPath(StorageUrl::objectPathFromStorageReference($source));
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

        if (! StorageUrl::isStorageReference($source)) {
            return false;
        }

        $objectPath = StorageUrl::objectPathFromStorageReference($source);

        return StorageUrl::isCloudDisk(StorageUrl::diskForObjectPath($objectPath));
    }
}
