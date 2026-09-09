<?php

namespace App\Console\Commands;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Models\ContentProposal;
use App\Models\User;
use Illuminate\Console\Command;

class ContentValidateProposalCommand extends Command
{
    protected $signature = 'content:validate-proposal {proposal} {--user=1}';

    protected $description = 'Validate a stored content proposal';

    public function handle(ContentAssistantOrchestrator $orchestrator): int
    {
        $proposal = ContentProposal::query()->findOrFail($this->argument('proposal'));
        $user = User::query()->findOrFail((int) $this->option('user'));
        $result = $orchestrator->validateProposal($proposal, $user);

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
