<?php

namespace App\ContentAssistant\Support;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ContentRevisionTracker
{
    public static function revisionNumber(Model $model): int
    {
        return match ($model::class) {
            Page::class => $model->revisions()->count(),
            Post::class => $model->revisions()->count(),
            Project::class => 0,
            default => 0,
        };
    }

    public static function hash(Model $model): string
    {
        $payload = match ($model::class) {
            Page::class => [
                'title' => $model->title,
                'nav_label' => $model->nav_label,
                'slug' => $model->slug,
                'blocks' => $model->blocks,
                'seo_title' => $model->seo_title,
                'seo_description' => $model->seo_description,
                'og_title' => $model->og_title,
                'og_description' => $model->og_description,
            ],
            Post::class => [
                'title' => $model->title,
                'slug' => $model->slug,
                'excerpt' => $model->excerpt,
                'body' => $model->body,
                'seo_title' => $model->seo_title,
                'seo_description' => $model->seo_description,
            ],
            Project::class => [
                'title' => $model->title,
                'slug' => $model->slug,
                'card_summary' => $model->card_summary,
                'blocks' => $model->blocks,
                'seo_title' => $model->seo_title,
                'seo_description' => $model->seo_description,
            ],
            default => ['updated_at' => (string) $model->updated_at],
        };

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
