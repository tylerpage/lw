<?php

namespace App\ContentAssistant\Support;

use App\Enums\ContentTargetType;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ContentTargetResolver
{
    public static function find(ContentTargetType $type, int $id): ?Model
    {
        return $type->modelClass()::query()->find($id);
    }

    public static function fromModel(Model $model): ContentTargetType
    {
        return match ($model::class) {
            Page::class => ContentTargetType::Page,
            Post::class => ContentTargetType::Post,
            Project::class => ContentTargetType::Project,
            default => throw new \InvalidArgumentException('Unsupported content target.'),
        };
    }

    public static function label(Model $model): string
    {
        return match ($model::class) {
            Page::class => "Page: {$model->title} ({$model->slug})",
            Post::class => "Post: {$model->title}",
            Project::class => "Case study: {$model->title}",
            default => class_basename($model),
        };
    }

    public static function publicUrl(Model $model): ?string
    {
        return match ($model::class) {
            Page::class => $model->slug === 'home' ? url('/') : url('/'.$model->slug),
            Post::class => url('/insights/'.$model->slug),
            Project::class => url('/work/'.$model->slug),
            default => null,
        };
    }
}
