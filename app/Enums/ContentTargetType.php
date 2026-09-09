<?php

namespace App\Enums;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;

enum ContentTargetType: string
{
    case Page = 'page';
    case Post = 'post';
    case Project = 'project';

    public function label(): string
    {
        return match ($this) {
            self::Page => 'Page',
            self::Post => 'Blog post',
            self::Project => 'Case study',
        };
    }

    public function modelClass(): string
    {
        return match ($this) {
            self::Page => Page::class,
            self::Post => Post::class,
            self::Project => Project::class,
        };
    }
}
