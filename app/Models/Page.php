<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasContentRevisions;
use App\Models\Concerns\HasPublishStatus;
use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasContentRevisions, HasPublishStatus, HasSeoFields;

    protected $fillable = [
        'title', 'nav_label', 'slug', 'status', 'has_unpublished_changes', 'published_revision_id', 'published_at', 'blocks',
        'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_image',
        'template', 'index', 'follow',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'blocks' => 'array',
            'has_unpublished_changes' => 'boolean',
            'index' => 'boolean',
            'follow' => 'boolean',
        ];
    }

    protected function revisionModelClass(): string
    {
        return PageRevision::class;
    }
}
