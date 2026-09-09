<?php

namespace App\Support;

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
            return $source;
        }

        if (str_starts_with($source, '//')) {
            return $source;
        }

        return asset(ltrim($source, '/'));
    }

    public static function isRemote(?string $source): bool
    {
        if ($source === null || trim($source) === '') {
            return false;
        }

        $source = trim($source);

        return filter_var($source, FILTER_VALIDATE_URL) !== false
            || str_starts_with($source, '//');
    }
}
