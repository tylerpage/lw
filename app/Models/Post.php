<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasContentRevisions;
use App\Models\Concerns\HasPublishStatus;
use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasContentRevisions, HasPublishStatus, HasSeoFields;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'status', 'has_unpublished_changes', 'published_revision_id', 'published_at', 'display_updated_at',
        'author_id', 'featured', 'reading_time_minutes', 'hero_image',
        'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_image',
        'index', 'follow',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'display_updated_at' => 'datetime',
            'body' => 'array',
            'has_unpublished_changes' => 'boolean',
            'featured' => 'boolean',
            'index' => 'boolean',
            'follow' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    protected function revisionModelClass(): string
    {
        return PostRevision::class;
    }

    public function readingTime(): int
    {
        return $this->reading_time_minutes ?? 5;
    }
}
