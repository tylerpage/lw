<?php

namespace App\Models;

use App\Support\MediaLibraryMimeTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'original_filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'alt_text',
    ];

    protected static function booted(): void
    {
        static::saving(function (MediaAsset $asset): void {
            if ($asset->path && $asset->disk) {
                $disk = Storage::disk($asset->disk);

                if ($disk->exists($asset->path)) {
                    $asset->mime_type ??= $disk->mimeType($asset->path) ?: null;
                    $asset->size = $disk->size($asset->path);
                }
            }

            $asset->original_filename ??= basename($asset->path);
            $asset->title ??= str($asset->original_filename)->beforeLast('.')->replace(['-', '_'], ' ')->title()->toString();

            if ($asset->mime_type && ! MediaLibraryMimeTypes::accepts($asset->mime_type, $asset->original_filename)) {
                throw new \InvalidArgumentException('This file type is not allowed in the media library.');
            }
        });

        static::deleting(function (MediaAsset $asset): void {
            Storage::disk($asset->disk)->delete($asset->path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publicPath(): string
    {
        return 'storage/'.$this->path;
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return MediaLibraryMimeTypes::isImage($this->mime_type);
    }

    public function isVisionAttachment(): bool
    {
        return MediaLibraryMimeTypes::isVisionAttachment($this->mime_type);
    }

    public function fileTypeLabel(): string
    {
        return MediaLibraryMimeTypes::label($this->mime_type, $this->original_filename);
    }

    /**
     * @return array{path: string, url: string, public_path: string, original_name: string, mime_type: string, size: int, media_asset_id: int, kind: string}
     */
    public function toAssistantAttachment(): array
    {
        return [
            'path' => $this->path,
            'url' => $this->url(),
            'public_path' => $this->publicPath(),
            'original_name' => $this->alt_text ?: $this->title ?: $this->original_filename,
            'mime_type' => $this->mime_type ?? 'application/octet-stream',
            'size' => (int) $this->size,
            'media_asset_id' => $this->id,
            'kind' => $this->isVisionAttachment() ? 'image' : 'reference',
        ];
    }
}
