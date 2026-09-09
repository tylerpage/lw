<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishStatus;
use App\Models\Concerns\HasSeoFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasPublishStatus, HasSeoFields;

    protected $fillable = [
        'title', 'slug', 'card_summary', 'status', 'published_at', 'featured', 'sort_order',
        'project_date', 'confidential', 'client_display_name', 'role', 'collaborators', 'blocks',
        'hero_image', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description',
        'og_image', 'index', 'follow',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'project_date' => 'date',
            'featured' => 'boolean',
            'confidential' => 'boolean',
            'blocks' => 'array',
            'index' => 'boolean',
            'follow' => 'boolean',
        ];
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ProjectMetric::class)->orderBy('sort_order');
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'project_discipline');
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class, 'project_industry');
    }

    public function displayClientName(): string
    {
        if ($this->confidential) {
            return 'Confidential Client';
        }

        return $this->client_display_name ?? 'Client';
    }
}
