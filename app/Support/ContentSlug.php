<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContentSlug
{
    public static function fromTitle(string $title, string $fallback = 'untitled'): string
    {
        $slug = Str::slug($title);

        return $slug !== '' ? $slug : $fallback;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function unique(
        string $title,
        string $modelClass,
        ?int $ignoreId = null,
        string $fallback = 'untitled',
    ): string {
        $base = self::fromTitle($title, $fallback);
        $candidate = $base;
        $suffix = 2;

        while (self::exists($modelClass, $candidate, $ignoreId)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    public static function ensure(
        array $data,
        string $modelClass,
        ?int $ignoreId = null,
        string $fallback = 'untitled',
    ): array {
        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug !== '') {
            $data['slug'] = $slug;

            return $data;
        }

        $data['slug'] = self::unique(
            title: (string) ($data['title'] ?? ''),
            modelClass: $modelClass,
            ignoreId: $ignoreId,
            fallback: $fallback,
        );

        return $data;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
