<?php

namespace App\Models\Concerns;

use App\Models\PageRevision;
use App\Models\PostRevision;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasContentRevisions
{
    public function publishedRevision(): BelongsTo
    {
        /** @var class-string<PageRevision|PostRevision> $revisionClass */
        $revisionClass = $this->revisionModelClass();

        return $this->belongsTo($revisionClass, 'published_revision_id');
    }

    /**
     * @return HasMany<PageRevision|PostRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany($this->revisionModelClass());
    }

    public function hasPendingDraft(): bool
    {
        return (bool) $this->has_unpublished_changes;
    }

    /**
     * @return class-string<PageRevision|PostRevision>
     */
    abstract protected function revisionModelClass(): string;
}
