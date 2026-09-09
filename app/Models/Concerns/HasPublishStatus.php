<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishStatus
{
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PublishStatus::Published)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Draft);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->where('status', PublishStatus::Scheduled)
            ->where('published_at', '>', now());
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->status !== PublishStatus::Published) {
            return false;
        }

        if ($this->published_at !== null && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }
}
