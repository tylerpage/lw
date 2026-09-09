<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishStatus;
use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasPublishStatus, HasSeoFields;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'status', 'published_at', 'display_updated_at',
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

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
    }

    public function readingTime(): int
    {
        return $this->reading_time_minutes ?? 5;
    }
}
