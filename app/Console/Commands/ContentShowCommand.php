<?php

namespace App\Console\Commands;

use App\ContentAssistant\Services\ContentContextBuilder;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Console\Command;

class ContentShowCommand extends Command
{
    protected $signature = 'content:show {type} {slug} {--format=text}';

    protected $description = 'Show structured content context for a record';

    public function handle(ContentContextBuilder $contextBuilder): int
    {
        $target = $this->resolveTarget($this->argument('type'), $this->argument('slug'));

        if (! $target) {
            $this->error('Content record not found.');

            return self::FAILURE;
        }

        $context = $contextBuilder->build($target);

        if ($this->option('format') === 'json') {
            $this->line(json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(ContentTargetResolver::label($target));
        $this->line(json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    private function resolveTarget(string $type, string $slug): Page|Post|Project|null
    {
        return match ($type) {
            'post' => Post::query()->where('slug', $slug)->first(),
            'project' => Project::query()->where('slug', $slug)->first(),
            default => Page::query()->where('slug', $slug)->first(),
        };
    }
}
