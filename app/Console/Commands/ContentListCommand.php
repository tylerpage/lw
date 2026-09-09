<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Console\Command;

class ContentListCommand extends Command
{
    protected $signature = 'content:list {--type=page : page, post, or project}';

    protected $description = 'List content records available to the assistant';

    public function handle(): int
    {
        $records = match ($this->option('type')) {
            'post' => Post::query()->orderBy('title')->get(['id', 'title', 'slug', 'status']),
            'project' => Project::query()->orderBy('title')->get(['id', 'title', 'slug', 'status']),
            default => Page::query()->orderBy('title')->get(['id', 'title', 'slug', 'status']),
        };

        $this->table(['ID', 'Title', 'Slug', 'Status'], $records->map(fn ($record) => [
            $record->id,
            $record->title,
            $record->slug,
            (string) $record->status->value,
        ]));

        return self::SUCCESS;
    }
}
