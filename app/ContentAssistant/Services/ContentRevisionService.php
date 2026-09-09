<?php

namespace App\ContentAssistant\Services;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContentRevisionService
{
    public function record(Page|Post $target, User $user, string $source, ?string $label = null): PageRevision|PostRevision
    {
        $snapshot = $this->snapshot($target);
        $resolvedLabel = $label ?? $this->defaultLabel($source);

        return match ($target::class) {
            Page::class => PageRevision::query()->create([
                'page_id' => $target->id,
                'user_id' => $user->id,
                'label' => $resolvedLabel,
                'source' => $source,
                'title' => $snapshot['title'] ?? $target->title,
                'blocks' => $snapshot['blocks'] ?? [],
                'snapshot' => $snapshot,
            ]),
            Post::class => PostRevision::query()->create([
                'post_id' => $target->id,
                'user_id' => $user->id,
                'label' => $resolvedLabel,
                'source' => $source,
                'data' => $snapshot,
            ]),
        };
    }

    public function applyToModel(Page|Post $target, PageRevision|PostRevision $revision): void
    {
        $snapshot = $this->revisionSnapshot($revision);

        foreach ($snapshot as $field => $value) {
            if (! in_array($field, $target->getFillable(), true)) {
                continue;
            }

            $target->{$field} = $value;
        }
    }

    public function pinPublishedRevision(Page|Post $target, PageRevision|PostRevision $revision): void
    {
        $target->published_revision_id = $revision->id;
        $target->has_unpublished_changes = true;
    }

    public function clearUnpublishedChanges(Page|Post $target): void
    {
        $target->has_unpublished_changes = false;
        $target->published_revision_id = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Page|Post $target): array
    {
        return match ($target::class) {
            Page::class => [
                'title' => $target->title,
                'nav_label' => $target->nav_label,
                'slug' => $target->slug,
                'blocks' => $target->blocks ?? [],
                'seo_title' => $target->seo_title,
                'seo_description' => $target->seo_description,
                'canonical_url' => $target->canonical_url,
                'og_title' => $target->og_title,
                'og_description' => $target->og_description,
                'og_image' => $target->og_image,
                'template' => $target->template,
                'index' => $target->index,
                'follow' => $target->follow,
            ],
            Post::class => [
                'title' => $target->title,
                'slug' => $target->slug,
                'excerpt' => $target->excerpt,
                'body' => $target->body ?? [],
                'seo_title' => $target->seo_title,
                'seo_description' => $target->seo_description,
                'canonical_url' => $target->canonical_url,
                'og_title' => $target->og_title,
                'og_description' => $target->og_description,
                'og_image' => $target->og_image,
                'featured' => $target->featured,
                'reading_time_minutes' => $target->reading_time_minutes,
                'hero_image' => $target->hero_image,
                'index' => $target->index,
                'follow' => $target->follow,
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function revisionSnapshot(PageRevision|PostRevision $revision): array
    {
        if ($revision instanceof PageRevision) {
            return $revision->snapshot ?? [
                'title' => $revision->title,
                'blocks' => $revision->blocks ?? [],
            ];
        }

        return $revision->data ?? [];
    }

    public function forPublicDisplay(Page|Post $target): Page|Post
    {
        if (! $target->has_unpublished_changes || ! $target->published_revision_id) {
            return $target;
        }

        $revision = $target->publishedRevision;

        if (! $revision) {
            return $target;
        }

        $public = clone $target;
        $this->applyToModel($public, $revision);

        return $public;
    }

    public function hydratePreviewRevision(Model $target, PageRevision|PostRevision $revision): Page|Post
    {
        $preview = clone $target;
        $this->applyToModel($preview, $revision);

        return $preview;
    }

    private function defaultLabel(string $source): string
    {
        return match ($source) {
            'ai_assistant' => 'Before AI changes',
            'content_import' => 'Before import',
            'manual_edit' => 'Before manual edit',
            'restore' => 'Before restore',
            'publish' => 'Published version',
            'discard_draft' => 'Before discarding draft',
            default => 'Saved version',
        };
    }
}
