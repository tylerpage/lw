<?php

namespace App\ContentAssistant\Services;

use App\Models\MediaAsset;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MediaLibraryService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function assetsForContext(int $limit = 50): array
    {
        return MediaAsset::query()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (MediaAsset $asset): array => [
                'id' => $asset->id,
                'title' => $asset->title,
                'public_path' => $asset->publicPath(),
                'url' => $asset->url(),
                'alt_text' => $asset->alt_text,
                'mime_type' => $asset->mime_type,
            ])
            ->all();
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, array<string, mixed>>
     */
    public function attachmentsForIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return MediaAsset::query()
            ->whereIn('id', $ids)
            ->get()
            ->map(fn (MediaAsset $asset): array => $asset->toAssistantAttachment())
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, MediaAsset>
     */
    public function selectableImages(int $limit = 40): Collection
    {
        return MediaAsset::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function resolvePublicPath(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $candidates = [$value];

        if (str_contains($value, '/storage/')) {
            $candidates[] = 'storage'.Str::after($value, '/storage');
        }

        if (str_starts_with($value, 'storage/')) {
            $candidates[] = ltrim(Str::after($value, 'storage/'), '/');
        }

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            $path = ltrim(str_replace('storage/', '', $candidate), '/');

            $asset = MediaAsset::query()->where('path', $path)->first();

            if ($asset) {
                return $asset->publicPath();
            }
        }

        return null;
    }
}
