<?php

namespace App\Console\Commands;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentTargetType;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;

class ContentProposeCommand extends Command
{
    protected $signature = 'content:propose {type} {slug} {--input=} {--message=} {--user=1}';

    protected $description = 'Generate a content proposal using the assistant services';

    public function handle(ContentAssistantOrchestrator $orchestrator): int
    {
        $target = $this->resolveTarget($this->argument('type'), $this->argument('slug'));
        $user = User::query()->findOrFail((int) $this->option('user'));

        if (! $target) {
            $this->error('Content record not found.');

            return self::FAILURE;
        }

        $message = $this->option('message');

        if ($this->option('input')) {
            $payload = json_decode(file_get_contents($this->option('input')), true, 512, JSON_THROW_ON_ERROR);
            $message = $payload['message'] ?? $message;
        }

        if (! $message) {
            $this->error('Provide --message or an input JSON file with a message field.');

            return self::FAILURE;
        }

        $conversation = $orchestrator->startConversation(
            $user,
            match ($this->argument('type')) {
                'post' => ContentTargetType::Post,
                'project' => ContentTargetType::Project,
                default => ContentTargetType::Page,
            },
            $target->id,
        );

        $proposal = $orchestrator->sendMessage($conversation, $user, $message);
        $validation = $orchestrator->validateProposal($proposal, $user);

        $this->line(json_encode([
            'proposal_id' => $proposal->id,
            'summary' => $proposal->summary,
            'status' => $proposal->status->value,
            'valid' => $validation['valid'],
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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
