<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishStatus;
use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasPublishStatus, HasSeoFields;

    protected $fillable = [
        'title', 'nav_label', 'slug', 'status', 'published_at', 'blocks',
        'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_image',
        'template', 'index', 'follow',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'blocks' => 'array',
            'index' => 'boolean',
            'follow' => 'boolean',
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class);
    }
}
