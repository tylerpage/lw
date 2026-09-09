<?php

namespace App\Models\Concerns;

trait HasSeoFields
{
    public function seoTitle(): string
    {
        return $this->seo_title ?: ($this->title ?? config('app.name'));
    }

    public function seoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function isIndexable(): bool
    {
        return (bool) ($this->index ?? true);
    }

    public function isFollowable(): bool
    {
        return (bool) ($this->follow ?? true);
    }
}
